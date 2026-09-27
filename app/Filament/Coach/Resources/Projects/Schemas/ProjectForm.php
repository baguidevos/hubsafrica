<?php

namespace App\Filament\Coach\Resources\Projects\Schemas;

use App\Filament\Sae\Resources\Projects\Schemas\ProjectForm as SaeProjectForm;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return SaeProjectForm::configure($schema);
    }
}
