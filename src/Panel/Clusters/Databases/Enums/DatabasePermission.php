<?php

namespace Panelis\Database\Panel\Clusters\Databases\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum DatabasePermission: string implements HasLabel
{
    case Browse = 'BrowseDatabase';

    case Read = 'ReadDatabase';

    case Edit = 'EditDatabase';

    case Add = 'AddDatabase';

    case Delete = 'DeleteDatabase';

    case Backup = 'BackupDatabase';

    case Download = 'DownloadDatabase';

    public function getLabel(): string
    {
        return __(sprintf('database::permission.name_%s', Str::snake($this->value)));
    }
}
