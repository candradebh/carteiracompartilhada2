<template>
    <app-layout>
        <!--Header-->
        <template #header>
            Relatório IRPF
        </template>
        <!--Subheader-->
        <template #subHeader>
            Relátorio utilizado para declaração do Imposto de renda
        </template>

        <!--Content-->
        <template #default>
            <t-form-content @submitted="save">
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

            <div class="overflow-x-auto scrollbar scrollbar-thin transition-size-medium" >
                <table class="table-container">
                    <thead>
                        <tr>
                            <td  v-for="item in tableHeader" :key="item.id"> {{item.label}} </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in posicaoAnual" :key="item.ativo.ticker" class="table-content-row table-content-zebra-row">
                            <td class="table-content-cell">{{item.ativo.ticker}}</td>
                            <td class="table-content-cell">{{item.ativo.cnpj}}</td>
                            <td class="table-content-cell">{{item.quantidade}}</td>
                        </tr>

                    </tbody>
                </table>
            
            </div>
           
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
      { id: "ativo", label: "Ativo", key: "nome", align: "center",  simpleSearchable:true, status: true, sortable: true },
      { id: "cnpj", label: "Tipo", key: "cnpj", align: "left", simpleSearchable:true, status: true, sortable: true },
      { id: "total", label: "Total", key: "total", align: "left", status: true, sortable: true }
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
