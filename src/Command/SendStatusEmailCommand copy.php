<?php
// src/Command/SendStatusEmailCommand.php
namespace App\Command;

use App\Entity\BaseDiffusions;
use App\Entity\Fichiers;
use App\Entity\BaseColisDetails;
use App\Repository\BaseDiffusionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address as MimeAddress;

#[AsCommand(
    name: 'app:send-colis-status-emails',
    description: 'Envoi des emails de statut de colis + purge des fichiers échus (sans vérification de statut).'
)]
class SendStatusEmailCommand extends Command
{
    public function __construct(
        private BaseDiffusionsRepository $diffusionRepo,
        private MailerInterface $mailer,
        private EntityManagerInterface $em,
        private Filesystem $fs,
        #[Autowire('%kernel.project_dir%/public/uploads/')]
        private string $uploadsRoot
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* ===== 1) Envoi des emails (inchangé) ===== */
        $diffusions = $this->diffusionRepo->createQueryBuilder('d')
            ->where('d.canaux = :canal')
            ->andWhere('d.send = 0')
            ->setParameter('canal', 'email')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();

        foreach ($diffusions as $diffusion) {
            /** @var BaseDiffusions $diffusion */
            $clientEmail = $diffusion->getDestinateurs()->getEmail();

            if ($clientEmail) {
                $email = (new TemplatedEmail())
                    ->from(new MimeAddress('sdaircargo1@gmail.com', 'SD Air Cargo'))
                    ->to($clientEmail)
                    ->subject('🔔 Mise à jour du statut de votre colis')
                    ->htmlTemplate('emails/notification_statut.html.twig')
                    ->context([
                        'statut' => $diffusion->getStatut()
                    ]);

                try {
                    $this->mailer->send($email);
                    $diffusion->setSend(true);
                    $diffusion->setDateDiffusion(new \DateTime());
                    $this->em->persist($diffusion);
                } catch (\Throwable $e) {
                    $output->writeln("Erreur pour {$clientEmail} : " . $e->getMessage());
                }
            }
        }

        $this->em->flush();
        $output->writeln('Emails envoyés avec succès.');

        /* ===== 2) PURGE FICHIERS PHYSIQUES (sans filtre de statut) ===== */
        $today = new \DateTimeImmutable('today');

        $eligibles = $this->em->getRepository(Fichiers::class)->createQueryBuilder('f')
            ->andWhere('f.dateSuppression <= :today')
            ->andWhere(('f.statut = :statut '))
            ->setParameter('today', $today)
            ->setParameter('statut', 0)
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();

        $nbColisPurges = 0;

        foreach ($eligibles as $f) {
            // Supprimer le QR code (chemin RELATIF à /public/uploads/)
            $this->deleteRelativeIfExists($f->getColis()->getQrCodePath(), $output);

            // Supprimer les photos des détails
            $details = $this->em->getRepository(BaseColisDetails::class)
                ->findBy(['colis' => $f->getColis()]);
            foreach ($details as $d) {
                $this->deleteRelativeIfExists($d->getPhoto(), $output);
            }

            $f->setStatut(true);
            $this->em->persist($f);

            // ⚠ aucune suppression/MAJ en base
            $nbColisPurges++;
        }

        $output->writeln("Purge fichiers terminée : {$nbColisPurges} colis traités.");
        return Command::SUCCESS;
    }

    /** Supprime un fichier par chemin RELATIF à /public/uploads/ (sécurisé) */
    private function deleteRelativeIfExists(?string $relativePath, OutputInterface $output): void
    {
        if (!$relativePath) return;

        $base = rtrim($this->uploadsRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $candidate = $base . ltrim($relativePath, DIRECTORY_SEPARATOR);

        // Sécurisation anti-traversal
        $baseReal = realpath($base) ?: $base;
        $candReal = realpath($candidate) ?: $candidate;

        if (\str_starts_with($candReal, $baseReal)) {
            if ($this->fs->exists($candReal)) {
                try {
                    $this->fs->remove($candReal);
                    $output->writeln("Supprimé: " . $relativePath);
                } catch (\Throwable $e) {
                    $output->writeln("Échec suppression {$relativePath} : " . $e->getMessage());
                }
            } else {
                $output->writeln("Absent (ok): " . $relativePath);
            }
        } else {
            $output->writeln("Refusé (hors uploads/): " . $relativePath);
        }
    }
}
