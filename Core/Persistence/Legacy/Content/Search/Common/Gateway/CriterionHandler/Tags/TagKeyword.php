<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\Tags;

use Netgen\TagsBundle\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\Tags;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as TagKeywordCriterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Tag keyword criterion handler.
 */
class TagKeyword extends Tags
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
        return $criterion instanceof TagKeywordCriterion;
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
        /** @var \Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\Value\TagKeywordValue $valueData */
        $valueData = $criterion->valueData;

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
            )->innerJoin(
                $this->dbHandler->quoteTable('eztags'),
                $subSelect->expr->eq(
                    $this->dbHandler->quoteColumn('keyword_id', 'eztags_attribute_link'),
                    $this->dbHandler->quoteColumn('id', 'eztags')
                )
            )->leftJoin(
                $this->dbHandler->quoteTable('eztags_keyword'),
                $subSelect->expr->lAnd(
                    $subSelect->expr->eq(
                        $this->dbHandler->quoteColumn('id', 'eztags'),
                        $this->dbHandler->quoteColumn('keyword_id', 'eztags_keyword')
                    ),
                    $subSelect->expr->eq(
                        $this->dbHandler->quoteColumn('status', 'eztags_keyword'),
                        $subSelect->bindValue(1, null, \PDO::PARAM_INT)
                    )
                )
            );

        if ($valueData !== null && !empty($valueData->languages)) {
            if ($valueData->useAlwaysAvailable) {
                $subSelect->where(
                    $subSelect->expr->lOr(
                        $subSelect->expr->in(
                            $this->dbHandler->quoteColumn('locale', 'eztags_keyword'),
                            $valueData->languages
                        ),
                        $subSelect->expr->eq(
                            $this->dbHandler->quoteColumn('main_language_id', 'eztags'),
                            $subSelect->expr->bitAnd(
                                $this->dbHandler->quoteColumn('language_id', 'eztags_keyword'),
                                -2 // -2 == PHP_INT_MAX << 1
                            )
                        )
                    )
                );
            } else {
                $subSelect->where(
                    $subSelect->expr->in(
                        $this->dbHandler->quoteColumn('locale', 'eztags_keyword'),
                        $valueData->languages
                    )
                );
            }
        }

        if ($criterion->operator == Criterion\Operator::LIKE) {
            $subSelect->where(
                $subSelect->expr->like(
                    $this->dbHandler->quoteColumn('keyword', 'eztags_keyword'),
                    $subSelect->bindValue($criterion->value[0])
                )
            );
        } else {
            $subSelect->where(
                $subSelect->expr->in(
                    $this->dbHandler->quoteColumn('keyword', 'eztags_keyword'),
                    $criterion->value
                )
            );
        }

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
