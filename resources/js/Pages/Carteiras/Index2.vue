<template>
    <app-layout>
        <!--Header-->
        <template #header>
            Minhas Carteiras
        </template>
        <!--Subheader-->
        <template #subHeader>
            Personalise sua tabela
        </template>
        <!--Content-->
        <template #default>
            <!-- <t-component-color-selector @selected-color="tableColor = $event"/> -->

            <data-table
                  :config="{
                      url: 'carteiras/all?page=1'
                  }"
                  
        :columns="[
        {
        label: 'ID',
        column: 'id',
        show: true,
            sort:{
              sortable: false,
              sortColumn: 'id',

            },

       },
        {
        label: 'Title',
        column: 'title',
         show: true,
            sort:{
             sortable: true,
             sortColumn: 'title',
             sortDir: 'asc',
            },
       },
       {
        label: 'CreatedAt',
        column: 'created_at',
        show: true,
             sort:{
                sortable: true,
                sortColumn: 'created_at',
                sortDir: 'asc',
             },
       },
       {
        label: 'UpdatedAt',
        column: 'updated_at',
        show: true,
             sort:{
                sortable: true,
                sortColumn: 'updated_at',
                sortDir: 'asc',
             },
       },
        ]
        "

    ></data-table>

            
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
    name: "Carteiras",
    components: {
        TUserCircleIcon, TButton, GridSection, TInputText, TInputGroup, AppLayout, TTable, TAvatar, SshPre},
    props: ['users','carteiras','usuario'],
    setup() {
    /*Table States*/
    const tableHeader = reactive([
      { label: "Ativo", key: "nome", align: "center",  simpleSearchable:true, status: true, sortable: true },
      { label: "Tipo", key: "descricao", align: "left", simpleSearchable:true, status: true, sortable: true },
      { label: "Data", key: "total", align: "left", status: true, sortable: true }
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
