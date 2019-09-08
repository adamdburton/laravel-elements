<?php

namespace Click\Elements\Services\EntitiesOld;

use Click\Elements\Services\EntityService;

class MigrationService
{
    public function generateMigration($schema)
    {
//        $old = $this->entityService->

        $path = database_path(sprintf('migrations/%s_%s_%s.php', date('Y_m_d'), date('His'), 'fwefwef'));

        return $path;
    }
}