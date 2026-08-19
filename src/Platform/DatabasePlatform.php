<?php

declare(strict_types=1);

namespace PDO4You\Platform;

/**
 * Define a interface para comportamentos específicos de cada SGBD (dialetos).
 */
interface DatabasePlatform
{
    /**
     * Retorna a query SQL correta para recuperar o último ID inserido.
     */
    public function getLastInsertIdSql(?string $sequence = null): string;
}
