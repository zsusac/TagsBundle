<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\Tags;

use Netgen\TagsBundle\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\Tags;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId as TagIdCriterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Tag ID criterion handler.
 */
class TagId extends Tags
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof TagIdCriterion;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion)
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select($this->dbHandler->quoteColumn('id', 'ezcontentobject'))
            ->from($this->dbHandler->quoteTable('ezcontentobject'))
            ->innerJoin(
                $this->dbHandler->quoteTable('eztags_attribute_link'),
                $subSelect->expr->lAnd(
                    array(
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('objectattribute_version', 'eztags_attribute_link'),
                            $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('object_id', 'eztags_attribute_link'),
                            $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                        ),
                    )
                )
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('keyword_id', 'eztags_attribute_link'),
                    $criterion->value
                )
            );

        $fieldDefinitionIds = $this->getSearchableFields($criterion->target);
        if ($fieldDefinitionIds !== null) {
            $subSelect->innerJoin(
                $this->dbHandler->quoteTable('ezcontentobject_attribute'),
                $subSelect->expr->lAnd(
                    array(
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('id', 'ezcontentobject_attribute'),
                            $this->dbHandler->quoteColumn('objectattribute_id', 'eztags_attribute_link')
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute'),
                            $this->dbHandler->quoteColumn('objectattribute_version', 'eztags_attribute_link')
                        ),
                    )
                )
            );

            $subSelect->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_attribute'),
                    $fieldDefinitionIds
                )
            );
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
