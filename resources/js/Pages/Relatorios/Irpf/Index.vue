<template>
    <app-layout>
        <!--Header-->
        <template #header>
            Relatório IRPF
        </template>
        <!--Subheader-->
        <template #subHeader>
            <t-form-content :reset-button="false" :submit-button="true" @submitted="save">
                <t-form-section
                    title="Escolha o ano de referência">
                    <t-input-group class="col-span-12 md:col-span-6" label="Ano" labelFor="ano">
                        <t-input-select
                            v-model="form.ano"
                            :options="anos"
                            optionsLabelKey="ano"
                            optionsValueKey="ano"
                            place-holder="Selecione o ano"
                        />
                    </t-input-group>
                </t-form-section>
            </t-form-content>
        </template>

        <!--Content-->
        <template #default>
            
            <!-- Ultima posição do ano -->    
            <t-table :content="posicaoAnual" :header="tableHeader" :features="tableFeatures">
            </t-table>
        
            <!-- Resultados das açoes -->   
            <t-table :content="resultadosAcoes" :header="tableHeaderAcoes" :features="tableFeaturesResultados">
            </t-table>
            
            <t-table :content="resultadosFii" :header="tableHeaderFiis" :features="tableFeaturesResultados">
            </t-table>
           
        </template>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import TTable from "@/Components/Table/TTable";
import GridSection from "@/Layouts/GridSection";
import TButton from "@/Components/Button/TButton";
import TFormSection from "@/Components/Form/TFormSection";
import TFormContent from "@/Components/Form/TFormContent";
import TInputGroup from "@/Components/Form/TInputGroup";
import TInputSelect from "@/Components/Form/Inputs/TInputSelect";
import { reactive } from "vue";

export default {
    name: "Irpf",
    components: {
        TFormSection,TFormContent,
        TButton, GridSection, AppLayout, TTable ,TInputGroup , TInputSelect },
    props: ['resultadosAcoes','resultadosFii', 'posicaoAnual', 'anos', 'ano'],
    data() {
      return {
         form: this.$inertia.form({
                _method: 'GET',
                ano: parseInt(this.ano)
            }),
      }
    },
    setup() {
    /*Table States*/
    const tableHeader = reactive([
      { id: "ticker", label: "Ativo", key: "ticker", align: "center",  status: true, sortable: false },
      { id: "cnpj", label: "CNPJ", key: "cnpj", align: "left", simpleSearchable:, status: true, sortable: true },
      { id: "precomedio", label: "Preço Médio", key: "precomedio", align: "right",  status: true, sortable: true },
      { id: "quantidade", label: "Quantidade", key: "quantidade", align: "center", status: true, sortable: true },
      { id: "total", label: "ToTal", key: "total", align: "right", status: true, sortable: true }
    ]);
    const tableHeaderAcoes = reactive([
      { id: "mes", label: "mes", key: "mes", align: "center",   status: true, sortable: false },
      { id: "compras", label: "compras", key: "compras", align: "left", status: true, sortable: false },
      { id: "vendas", label: "vendas", key: "vendas", align: "right",  status: true, sortable: false },
      { id: "resultado", label: "resultado", key: "resultado", align: "center", status: true, sortable: false },
      { id: "prejuizoacumulado", label: "prejuizoacumulado", key: "prejuizoacumulado", align: "right", status: true, sortable: false }
    ]);

     const tableHeaderFiis = reactive([
      { id: "mes", label: "mes", key: "mes", align: "center",   status: true, sortable: false },
      { id: "compras", label: "compras", key: "compras", align: "left", status: true, sortable: false },
      { id: "vendas", label: "vendas", key: "vendas", align: "right",  status: true, sortable: false },
      { id: "resultado", label: "resultado", key: "resultado", align: "center", status: true, sortable: false },
      { id: "prejuizoacumulado", label: "prejuizoacumulado", key: "prejuizoacumulado", align: "right", status: true, sortable: false }
    ]);
    
    const tableFeatures = reactive({
      table: {
        design: "elegant",
        seperatedRow: true,
        rowBorder: true,
        zebraRow: true,
        radius: 3,
        perPage: 12
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

    const tableFeaturesResultados = reactive({
      table: {
        design: "elegant",
        seperatedRow: true,
        rowBorder: true,
        zebraRow: true,
        radius: 3,
        perPage: 12
      },
      pagination: {
        status: false,
        radius: 3,
        range: 5,
        jump: true,
      }
    });

    return { tableHeader,tableHeaderAcoes, tableHeaderFiis, tableFeatures, tableFeaturesResultados };
  },
  methods: {
        save() {
            this.form.get(route('relatorio.irpf.index'), {
                errorBag: 'customer',
                preserveScroll: true,
            });
            this.loading = true;
            this.form.ano = parseInt(this.ano);
        }
    },
   
}
</script>

<style scoped>

</style>
