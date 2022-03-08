<?php

namespace App\Observers;

use App\Models\CarteiraAnualIrpf;

class IrpfObserver
{   


    //public $afterCommit = true;

    /**
     * Handle the CarteiraAnualIrpf "created" event.
     *
     * @param  \App\Models\CarteiraAnualIrpf  $carteiraAnualIrpf
     * @return void
     */
    public function created(CarteiraAnualIrpf $carteiraAnualIrpf)
    {
        $carteiraAnualIrpf->ticker = $carteiraAnualIrpf->ativo->ticker;
        $carteiraAnualIrpf->cnpj = $carteiraAnualIrpf->ativo->cnpj;
        $carteiraAnualIrpf->total = $carteiraAnualIrpf->quantidade * $carteiraAnualIrpf->precomedio;
        $carteiraAnualIrpf->save();
    }

    /**
     * Handle the CarteiraAnualIrpf "updated" event.
     *
     * @param  \App\Models\CarteiraAnualIrpf  $carteiraAnualIrpf
     * @return void
     */
    public function updated(CarteiraAnualIrpf $carteiraAnualIrpf)
    {
        $carteiraAnualIrpf->ticker = $carteiraAnualIrpf->ativo->ticker;
        $carteiraAnualIrpf->cnpj = $carteiraAnualIrpf->ativo->cnpj;
        $carteiraAnualIrpf->total = $carteiraAnualIrpf->quantidade * $carteiraAnualIrpf->precomedio;
        $carteiraAnualIrpf->save();
    }

    /**
     * Handle the CarteiraAnualIrpf "deleted" event.
     *
     * @param  \App\Models\CarteiraAnualIrpf  $carteiraAnualIrpf
     * @return void
     */
    public function deleted(CarteiraAnualIrpf $carteiraAnualIrpf)
    {
        //
    }

    /**
     * Handle the CarteiraAnualIrpf "restored" event.
     *
     * @param  \App\Models\CarteiraAnualIrpf  $carteiraAnualIrpf
     * @return void
     */
    public function restored(CarteiraAnualIrpf $carteiraAnualIrpf)
    {
        //
    }

    /**
     * Handle the CarteiraAnualIrpf "force deleted" event.
     *
     * @param  \App\Models\CarteiraAnualIrpf  $carteiraAnualIrpf
     * @return void
     */
    public function forceDeleted(CarteiraAnualIrpf $carteiraAnualIrpf)
    {
        //
    }


    public function camposCalculados($carteiraAnualIrpf){
        $carteiraAnualIrpf->ticker = $carteiraAnualIrpf->ativo->ticker;
        $carteiraAnualIrpf->cnpj = $carteiraAnualIrpf->ativo->cnpj;
        $carteiraAnualIrpf->total = $carteiraAnualIrpf->quantidade * $carteiraAnualIrpf->precomedio;
    }
}
