<template>
    <app-layout>
        <!--Header-->
        <template #header>
            {{carteiras.nome}}
        </template>
        <!--Subheader-->
        <template #subHeader>
             {{carteiras.descricao}}
        </template>
        <!--Content-->
        <template #default>
            <!-- <t-component-color-selector @selected-color="tableColor = $event"/> -->

            <t-table :content="ativos" :header="tableHeader" :features="tableFeatures">
                <template #search>
                    <grid-section :col="12" :gap="2">
                        <!--Name-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Nome">
                            <t-input-text id="nome"/>
                        </t-input-group>
                        <!--Email-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Categoria">
                            <t-input-text id="categoria"/>
                        </t-input-group>
                    </grid-section>
                </template>

                <template>
                  <td>{{cellKey.props.quantidade}}</td>
                  <td>{{cellKey.props.total}}</td>
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
    name: "ShowCarteira",
    components: {
        TUserCircleIcon, TButton, GridSection, TInputText, TInputGroup, AppLayout, TTable, TAvatar, SshPre},
    props: ['users','carteiras','usuario','ativos'],
    setup() {
    /*Table States*/
    const tableHeader = reactive([
      { label: "Ticker", key: "ticker", align: "center",  simpleSearchable:true, status: true, sortable: true },
      { label: "Categoria", key  : "categoria", align: "left", simpleSearchable:true, status: true, sortable: true },
      { label: "Cotação", key: "cotacao", align: "left", status: true, sortable: false },
      { label: "Quantidade", key: "quantidade", id: "quantidade", align: "center", status: true, sortable: false },
      { label: "Total", key: "total", id: "total", align: "right", status: true, sortable: false }
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
