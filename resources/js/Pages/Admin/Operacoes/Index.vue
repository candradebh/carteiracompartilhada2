<template>
    <app-layout>
        <!--Header-->
        <template #header>
            {{ tituloPagina}}
        </template>
        <!--Subheader-->
        <template #subHeader>
            {{subTitulo}}
        </template>
        <!--Content-->
        <template #default>

            <TVueTable
                :content="operacoes"
                :header="header"
                :color="tableColor"
            ></TVueTable>

        </template>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import 'simple-syntax-highlighter/dist/sshpre.css'
import TVueTable from '@/Components/Table/TVueTable'



export default {
    name: "Operacoes",
    components: {
        AppLayout, TVueTable
    },
    props: ['operacoes'],
    data() {
        return {
            tituloPagina: 'Operações',
            subTitulo: 'Operações de fusão, split, inplit',
            tableColor: 'solid-blue',
            header: [
                {
                    name: 'ativo_id',
                    sortField: 'ativo_id',
                    titleClass: 'center aligned border',
                    dataClass: 'border-collapse border border-slate-400'
                },
                {
                    name: 'tipooperacao',
                    title: 'Tipo',
                    titleClass: 'center aligned border',
                    dataClass: 'border-collapse border border-slate-400'
                },
                {
                    name: 'data',
                    sortField: 'data',
                    titleClass: 'center aligned border',
                    dataClass: 'border-collapse border border-slate-400'
                },
            ]
        }
    },
    computed: {
        selectorInnerStyle() {
            return 'flex-shrink-0 w-full text-center bg-opacity-50 px-2 py-1 bg-white rounded-full font-semibold z-10'
        }
    }
}
</script>

<style scoped>

</style>
