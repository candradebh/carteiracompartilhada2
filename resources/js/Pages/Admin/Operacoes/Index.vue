<template>
    <app-layout>
        <!--Header-->
        <template #header>
            Operações
        </template>
        <!--Subheader-->
        <template #subHeader>
            Fusão, Split ou Inplit
        </template>
        <!--Content-->
        <template #default>
            <t-table  :content="operacoes" :header="tableHeader"  :features="tableFeatures" >
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
import { reactive } from "vue";

export default {
    name: "Operacoes",
    components: {
        TUserCircleIcon, TButton, GridSection, TInputText, TInputGroup, AppLayout, TTable, TAvatar, SshPre},
    props: ['operacoes'],
     setup() {
    /*Table States*/
    const tableHeader = reactive([
      { label: "Ativo", key: "ativo_id", align: "center", width: "5", status: true },
      { label: "Tipo", key: "tipooperacao", align: "left", simpleSearchable:true, status: true, sortable: true },
      { label: "Data", key: "data", align: "left", status: true, sortable: true }
    ]);
    const tableFeatures = reactive({
      table: {
        design: "elegant",
        seperatedRow: true,
        rowBorder: true,
        zebraRow: true,
        radius: 3,
        perPage: 5
      },
      pagination: {
        status: true,
        radius: 3,
        range: 5,
        jump: true,
      },
      actions: {
        status: true,
        headerText: "Aksiyonlar"
      },
      deleteModal: {
        headerText: "Item's deleting",
        contentText: "You are going to delete <br><b></b><br>Are you sure ?",
        icon: "warning"
      }
    });

    return { tableHeader, tableFeatures };
  }
   
}
</script>

<style scoped>

</style>
