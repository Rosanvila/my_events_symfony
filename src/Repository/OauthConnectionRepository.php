<?php

namespace App\Repository;

use App\Entity\OauthConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class OauthConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OauthConnection::class);
    }

    public function save(OauthConnection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OauthConnection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByProviderAndProviderId(string $provider, string $providerId): ?OauthConnection
    {
        return $this->findOneBy([
            'provider' => $provider,
            'providerId' => $providerId
        ]);
    }

    public function findOneByProviderAndEmail(string $provider, string $email): ?OauthConnection
    {
        return $this->findOneBy([
            'provider' => $provider,
            'email' => $email
        ]);
    }
}
