<?php

namespace App\Repository;

use App\Entity\ClinicScheduleSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClinicScheduleSettings>
 */
class ClinicScheduleSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClinicScheduleSettings::class);
    }

    public function getOrCreateDefault(): ClinicScheduleSettings
    {
        $settings = $this->find(1);
        if ($settings !== null) {
            return $settings;
        }

        $settings = new ClinicScheduleSettings();
        $this->getEntityManager()->persist($settings);
        $this->getEntityManager()->flush();

        return $settings;
    }
}
