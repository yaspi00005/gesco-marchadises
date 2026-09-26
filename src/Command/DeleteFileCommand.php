<?php
// src/Command/SendStatusEmailCommand.php
namespace App\Command;

use App\Entity\Fichiers;
use App\Entity\BaseColisDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:DeleteFileCommand',
    description: 'Purge des fichiers échus: supprime les fichiers du disque, ne modifie pas les colonnes image en base.'
)]
class DeleteFileCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private Filesystem $fs,
        #[Autowire('%kernel.project_dir%/public/uploads/')]
        private string $uploadsRoot
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTimeImmutable('today');

        /** @var Fichiers[] $eligibles */
        $eligibles = $this->em->getRepository(Fichiers::class)->createQueryBuilder('f')
            ->andWhere('f.dateSuppression <= :today')
            ->andWhere('f.statut != true OR f.statut IS NULL')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        $nbColisPurges = 0;
        $traiteColisIds = [];

        foreach ($eligibles as $f) {
            $colis = $f->getColis();

            if (!$colis) {
                // Fichier orphelin : on marque juste comme traité
                $f->setStatut(true);
                $this->em->persist($f);
                continue;
            }

            $colisId = $colis->getId();
            if (isset($traiteColisIds[$colisId])) {
                // Déjà nettoyé pour ce colis pendant ce run
                $f->setStatut(true);
                $this->em->persist($f);
                continue;
            }

            // 1) Supprimer le QR code (fichier uniquement)
            $this->deleteRelativeIfExists($colis->getQrCodePath(), $output);

            // 2) Supprimer les photos des détails (fichiers uniquement)
            $details = $this->em->getRepository(BaseColisDetails::class)
                ->findBy(['colis' => $colis]);

            foreach ($details as $d) {
                $this->deleteRelativeIfExists($d->getPhoto(), $output);
            }

            // 3) Marquer la ligne Fichiers comme traitée
            $f->setStatut(true);
            $this->em->persist($f);

            $traiteColisIds[$colisId] = true;
            $nbColisPurges++;
        }

        $this->em->flush();
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
