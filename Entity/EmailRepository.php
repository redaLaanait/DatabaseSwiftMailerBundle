<?php

namespace Citrax\Bundle\DatabaseSwiftMailerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EmailRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EmailRepository extends EntityRepository
{
    public function addEmail(Email $email){
        $em = $this->getEntityManager();
        $email->setStatus(Email::STATUS_READY);
        $email->setRetries(0);
        $em->persist($email);
        $em->flush();
    }

    public function getAllEmails(){
        $qb = $this->createQueryBuilder('e');

        $qb->addOrderBy('e.createdAt','DESC');
        return $qb->getQuery();
    }

    public function getEmailQueue($limit = 100){
        $qb = $this->createQueryBuilder('e');

        $qb->where($qb->expr()->eq('e.status',':status'))->setParameter(':status' ,Email::STATUS_READY);
        $qb->orWhere($qb->expr()->eq('e.status',':status_1'))->setParameter(':status_1' ,Email::STATUS_FAILED);
        $qb->andWhere($qb->expr()->lt('e.retries',':retries'))->setParameter(':retries',10);


        $qb->addOrderBy('e.retries','ASC');
        $qb->addOrderBy('e.createdAt','ASC');
        if(empty($limit) === false){
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function markFailedSending(Email $email, \Exception $ex){
        $email->setErrorMessage($ex->getMessage());
        $email->setStatus(Email::STATUS_FAILED);
        $email->setRetries($email->getRetries()+1);
        $em = $this->getEntityManager();
        $em->persist($email);
        $em->flush();
    }

    public function markCompleteSending(Email $email){
        $email->setStatus(Email::STATUS_COMPLETE);
        $email->setSentAt(new \DateTime());
        $em = $this->getEntityManager();
        $em->persist($email);
        $em->flush();
    }



}
