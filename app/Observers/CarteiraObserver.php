<?php

namespace App\Observers;

use App\Models\Carteira;

class CarteiraObserver
{
    /**
     * Handle the Carteira "created" event.
     *
     * @param  \App\Models\Carteira  $carteira
     * @return void
     */
    public function created(Carteira $carteira)
    {
        //
    }

    /**
     * Handle the Carteira "updated" event.
     *
     * @param  \App\Models\Carteira  $carteira
     * @return void
     */
    public function updated(Carteira $carteira)
    {
        //
    }

    /**
     * Handle the Carteira "deleted" event.
     *
     * @param  \App\Models\Carteira  $carteira
     * @return void
     */
    public function deleted(Carteira $carteira)
    {
        //
    }

    /**
     * Handle the Carteira "restored" event.
     *
     * @param  \App\Models\Carteira  $carteira
     * @return void
     */
    public function restored(Carteira $carteira)
    {
        //
    }

    /**
     * Handle the Carteira "force deleted" event.
     *
     * @param  \App\Models\Carteira  $carteira
     * @return void
     */
    public function forceDeleted(Carteira $carteira)
    {
        //
    }
}
