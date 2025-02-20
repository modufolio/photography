<?php

namespace AppBundle\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationHelper
{
    /**
     * Return of pages per query.
     *
     * @param Query $query    The Query
     * @param int   $pageSize Count of elements per page
     *
     * @return int Page count
     */
    public static function getPagesCount(Query $query, $pageSize = 10)
    {
        $paginator = new Paginator($query);

        // Disable for performance reasons
        $paginator->setUseOutputWalkers(false);

        return ceil($paginator->count() / $pageSize);
    }

    /**
     * Returns the site result for a query.
     *
     * http://stackoverflow.com/questions/24598567/doctrine-orm-pagination-and-use-with-twig
     *
     * @param Query $query       The Query
     * @param int   $pageSize    Count of elements per page
     * @param int   $currentPage The current page
     *
     * @return array The result
     */
    public static function paginate(Query $query, $pageSize = 10, $currentPage = 1)
    {
        $pageSize = (int) $pageSize;
        $currentPage = (int) $currentPage;

        if ($pageSize < 1) {
            $pageSize = 10;
        }

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $paginator = new Paginator($query);

        $results = $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($currentPage - 1))
            ->setMaxResults($pageSize)
            ->getResult();

        return $results;
    }
}
