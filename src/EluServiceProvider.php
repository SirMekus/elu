<?php
 
namespace Laravel\Elu;
 
use Illuminate\Support\ServiceProvider;
 
class EluServiceProvider extends ServiceProvider
{
    public function boot()
{
    $this->publishes([
        __DIR__.'/../config/elu.php' => config_path('elu.php'),
    ]);
}
}