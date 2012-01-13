<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\PropelBundle\Model\Acl;

use Criteria;
use PropelPDO;
use PropelCollection;
use InvalidArgumentException;

use Propel\PropelBundle\Model\Acl\om\BaseEntryQuery;
use Propel\PropelBundle\Model\Acl\EntryPeer;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class EntryQuery extends BaseEntryQuery
{
    /**
     * Return Entry objects filtered by an ACL related ObjectIdentity.
     *
     * @see find()
     *
     * @param ObjectIdentityInterface $objectIdentity A list of ObjectIdentityInterface to retrieve the ACL for.
     * @param array $securityIdentities A list of SecurityIdentity to filter by.
     * @param PropelPDO $con
     *
     * @return PropelCollection
     */
    public function findByAclIdentity(ObjectIdentityInterface $objectIdentity, array $securityIdentities = array(), PropelPDO $con = null)
    {
        $securityIds = array();
        foreach ($securityIdentities as $eachIdentity) {
            if (!$eachIdentity instanceof SecurityIdentityInterface) {
                throw new InvalidArgumentException(sprintf('The list of security identities contains at least one invalid entry of class "%s". Please provide objects of classes implementing "Symfony\Component\Security\Acl\Model\SecurityIdentityInterface" only.', get_class($eachIdentity)));
            }

            if ($securityIdentity = SecurityIdentity::fromAclIdentity($eachIdentity)) {
                $securityIds[$securityIdentity->getId()] = $securityIdentity->getId();
            }
        }

        $this
            ->useAclClassQuery(null, Criteria::INNER_JOIN)
                ->filterByType($objectIdentity->getType())
            ->endUse()
            ->useObjectIdentityQuery(null, Criteria::INNER_JOIN)
                ->filterByIdentifier($objectIdentity->getIdentifier())
            ->endUse()
        ;

        if (!empty($securityIdentities)) {
            $this->filterBySecurityIdentityId($securityIds);
        }

        return $this->find($con);
    }
}
