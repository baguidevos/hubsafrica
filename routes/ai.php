<?php

use App\Mcp\Servers\SaeMcpServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| AI / MCP Routes — Hubs Africa
|--------------------------------------------------------------------------
|
| Exposition du serveur MCP pour les Structures d'Accompagnement (SAEs).
| Les requêtes sont authentifiées via la clé API Bearer de la structure.
|
*/

Mcp::web('/mcp/sae', SaeMcpServer::class)
    ->middleware(['auth.mcp']);
