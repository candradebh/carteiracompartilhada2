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
            <t-component-color-selector @selected-color="tableColor = $event"/>

            <t-table :color="tableColor" :content="operacoes" :header="header" :pagination="true"
                     :searchable="['ativo_id','tipooperacao']">
                <template #search>
                    <grid-section :col="12" :gap="2">
                        <!--Name-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Ativo">
                            <t-input-text id="ativo_id"/>
                        </t-input-group>
                        <!--Email-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Tipo">
                            <t-input-text id="tipooperacao"/>
                        </t-input-group>
                    </grid-section>
                </template>
                <template #right>
                    <t-button :link="route('form-structure')" :radius="8">
                        <t-user-circle-icon class="w-6 h-6"/>
                        Adicionar
                    </t-button>
                </template>

            </t-table>

        </template>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import TTable from "@/Components/Table/TTable";
import TAvatar from "@/Components/Avatar/TAvatar";
import SshPre from 'simple-syntax-highlighter'
import 'simple-syntax-highlighter/dist/sshpre.css'
import TInputGroup from "@/Components/Form/TInputGroup";
import TInputText from "@/Components/Form/Inputs/TInputText";
import GridSection from "@/Layouts/GridSection";
import TButton from "@/Components/Button/TButton";
import TUserCircleIcon from "@/Components/Icon/TUserCircleIcon";
import TComponentColorSelector from "@/Components/Misc/TComponentColorSelector";

export default {
    name: "Operacoes",
    components: {
        TComponentColorSelector,
        TUserCircleIcon, TButton, GridSection, TInputText, TInputGroup, AppLayout, TTable, TAvatar, SshPre},
    props: ['operacoes'],
    data() {
        return {
            tituloPagina: 'Operações',
            subTitulo: 'Operações de fusão, split, inplit',
            tableColor: 'solid-blue',
            header: [
                {label: 'Ativo', key: 'ativo_id', align: 'center'},
                {label: 'Tipo', key: 'tipooperacao', align: 'center', width: '5'},
                {label: 'Data', key: 'data', align: 'left'},
                {label: 'Proporcão', key: 'proporcao', align: 'left'}
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
