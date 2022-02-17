<template>
    <app-layout>
        <!--Header-->
        <template #header>
            Carteiras
        </template>
        <!--Subheader-->
        <template #subHeader>
            Personalise sua tabela
        </template>
        <!--Content-->
        <template #default>
            <!-- <t-component-color-selector @selected-color="tableColor = $event"/> -->

            <t-table :color="tableColorMinhasCarteiras" :content="carteiras" :header="headerCarteiras" :pagination="true"
                     :searchable="['nome','descricao']">
                <template #search>
                    <grid-section :col="12" :gap="2">
                        <!--Name-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Nome">
                            <t-input-text id="nome"/>
                        </t-input-group>
                        <!--Email-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Descricão">
                            <t-input-text id="descricao"/>
                        </t-input-group>
                    </grid-section>
                </template>
                <template #right>
                    <t-button :link="route('form-structure')" :radius="8">
                        <t-user-circle-icon class="w-6 h-6"/>
                        Nova Carteira
                    </t-button>
                </template>
                <template #photo="{props}">
                        <t-avatar :link="props.photo" :radius="8" :size="3"/>
                </template>
            </t-table>

            <!--Carteiras compartilhadas-->
            <t-table :color="tableColorMinhasCarteiras" :content="usuario.carteiras_compartilhadas" :header="headerCarteiras" :pagination="true"
                     :searchable="['nome','descricao']">
                <template #search>
                    <grid-section :col="12" :gap="2">
                        <!--Name-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Nome">
                            <t-input-text id="nome"/>
                        </t-input-group>
                        <!--Email-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Descricão">
                            <t-input-text id="descricao"/>
                        </t-input-group>
                    </grid-section>
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
    name: "Carteiras",
    components: {
        TComponentColorSelector,
        TUserCircleIcon, TButton, GridSection, TInputText, TInputGroup, AppLayout, TTable, TAvatar, SshPre},
    props: ['users','carteiras','usuario'],
    data() {
        return {
            tableColorMinhasCarteiras: 'solid-blue',
            tableColorCarteirasCompartilhadas: 'solid-red',
            headerCarteiras: [
                {label: 'Nome', key: 'nome', align: 'left', width: '20'},
                {label: 'Descrição', key: 'descricao', align: 'left' },
                {label: 'Total', key: 'total', align: 'right', width: '5'},
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
