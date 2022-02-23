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

            <t-component-color-selector @selected-color="tableColor = $event"/>

            <t-table :color="tableColor" :data="posicaoAnual" :headers="headerPosicaoAtual" :pagination="false">
            </t-table>

            <t-table :color="tableColor" :data="resultadosAcoes" :headers="headerAcoes" :pagination="false" >
            </t-table>
        </template>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
//import TTable from "@/Components/Table/TTable";
import GridSection from "@/Layouts/GridSection";
import TButton from "@/Components/Button/TButton";
import TComponentColorSelector from "@/Components/Misc/TComponentColorSelector";
import TFormSection from "@/Components/Form/TFormSection";
import TFormContent from "@/Components/Form/TFormContent";
import TInputGroup from "@/Components/Form/TInputGroup";
import TInputSelect from "@/Components/Form/Inputs/TInputSelect";
import TTable from 'vue-tailwind/dist/t-table';

export default {
    name: "Irpf",
    components: {
        TComponentColorSelector,TFormSection,TFormContent,
        TButton, GridSection, AppLayout, TTable ,TInputGroup , TInputSelect },
    props: ['resultadosAcoes','resultadosFii', 'posicaoAnual', 'anos', 'buscaAno'],
    data() {
        return {
            pagedItem: 12,
            loading: false,
            form: this.$inertia.form({
                _method: 'GET',
                ano: parseInt(this.buscaAno)
            }),
            tableColor: 'solid-blue',
            headerPosicaoAtual: [
                {text: 'Ticker', value: 'ativo_id', id: 'ativo_id', className: 'bg-red-200'},
                {text: 'Cnpj', value: 'cnpj', id: 'cnpj', className: 'bg-red-200'},
                {text: 'Quantidade', value: 'quantidade', id: 'quantidade', className: 'bg-red-200'},
                {text: 'Total', value: 'name', id: 'total', className: 'bg-red-200'}
            ],
            headerAcoes: [
                {text: 'Mês', value: 'mes', id: 'mes-id', className: 'bg-red-200'},
                {text: 'Compras', value: 'compras', id: 'compras', className: 'bg-red-200'},
                {text: 'Vendas', value: 'vendas', id: 'vendas', className: 'bg-red-200'},
                {text: 'Resultado', value: 'resultado', id: 'resultado', className: 'bg-red-200'}
            ],


            classes: {
                table: 'min-w-full divide-y divide-gray-100 shadow-sm border-gray-200 border',
                thead: '',
                theadTr: '',
                theadTh: 'px-3 py-2 font-semibold text-left bg-gray-100 border-b',
                tbody: 'bg-white divide-y divide-gray-100',
                tr: '',
                td: 'px-3 py-2 whitespace-no-wrap',
                tfoot: '',
                tfootTr: '',
                tfootTd: ''
            },
            variants: {
                thin: {
                td: 'p-1 whitespace-no-wrap text-sm',
                theadTh: 'p-1 font-semibold text-left bg-gray-100 border-b text-sm'
                }
            }

        }
    },

    computed: {
        selectorInnerStyle() {
            return 'flex-shrink-0 w-full text-center bg-opacity-50 px-2 py-1 bg-white rounded-full font-semibold z-10'
        }
    },
    methods: {

        save() {
            this.form.get(route('relatorio.irpf.index'), {
                errorBag: 'customer',
                preserveScroll: true,
            });
            this.loading = true;
            this.form.ano = parseInt(this.buscaAno);

        }
    },
}
</script>

<style scoped>

</style>
