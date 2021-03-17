<?php

namespace App\Console\Commands;

use Laravel\Passport\Console\PurgeCommand;

class PurgeTokens extends PurgeCommand
{

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'tokens:purge
                          {--revoked : Only purge revoked tokens and authentication codes}
                          {--expired : Only purge expired tokens and authentication codes}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Purge revoked and / or expired tokens and authentication codes';
}
