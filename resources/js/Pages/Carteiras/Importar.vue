<template>
  <app-layout :actionButtons="true">
    <template #header>
      Importar Notas
    </template>
    <template #action-buttons>
      <TButton :radius="3" color="solid-yellow">
        <font-awesome-icon icon="angle-left"/>
        Back to Home
      </TButton>
      <TButton :radius="3" color="solid-green">
        <font-awesome-icon icon="plus-circle"/>
        Add New
      </TButton>
    </template>
    <template #default>
      <t-form-content :reset-button="false"  @reset="reset" @submitted="save">
        <!--Form Content Area Indicator-->
        <t-form-section
            description="Importe os arquivos de corretagem"
            title="Importador">
            <!--Form Section Area Indicator-->
            <div class="col-span-full">
                <t-badge color="solid-blue">Form Section Area</t-badge>
            </div>

            <t-input-group class="col-span-12 lg:col-span-6" label="Tipo de Importação" labelFor="tipo" >
                <div class="inline-flex flex-wrap items-center gap-2">
                    <t-input-radio-button
                        modelValue="form.tipo"
                        value="todos"
                        :radius="3"
                        color="solid-green"
                        input-value="todos"
                        label="Todos"
                        id="tipo"
                    >
                        <template #icon>
                            <t-x-icon class="w-5 h-5"/>
                        </template>
                    </t-input-radio-button>

                    <t-input-radio-button
                        modelValue="form.tipo"
                        value="arquivo"
                        :radius="3"
                        color="solid-red"
                        input-value="arquivo"
                        label="Arquivos"
                        id="tipo"
                    >
                        <template #icon>
                            <t-x-icon class="w-5 h-5"/>
                        </template>
                    </t-input-radio-button>

                </div>
            </t-input-group>


            <!-- Corretora -->
            <t-input-group class="col-span-12 md:col-span-6" label="Corretora" labelFor="corretora_id">
                <t-input-select
                    v-model="form.corretora_id"
                    :clear-button="true"
                    :options="corretoras"
                    optionsLabelKey="nome"
                    optionsValueKey="id"
                    place-holder="Selecione a Corretora"
                />
            </t-input-group>

            <!-- Carteira -->
            <t-input-group class="col-span-12 md:col-span-6" label="Carteira" labelFor="carteira_id">
                <t-input-select
                    v-model="form.carteira_id"
                    :clear-button="true"
                    :options="carteiras"
                    optionsLabelKey="nome"
                    optionsValueKey="id"
                    place-holder="Selecione a Carteira"
                />
            </t-input-group>

            <!-- Nota de corretagem -->
            <t-input-group class="col-span-12 lg:col-span-6" label="Updaload Nota" labelFor="upload">
                <t-input-file :preview="true" id="upload" v-model="form.upload" :multiple="true"/>
            </t-input-group>

        </t-form-section>
    </t-form-content>

    </template>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import TButton from "@/Components/Button/TButton";
import TFormSection from "@/Components/Form/TFormSection";
import TFormContent from "@/Components/Form/TFormContent";
import TInputGroup from "@/Components/Form/TInputGroup";
import TInputRadioButton from "@/Components/Form/Inputs/TInputRadioButton";
import TInputText from "@/Components/Form/Inputs/TInputText";
import TInputTextArea from "@/Components/Form/Inputs/TInputTextArea";
import 'simple-syntax-highlighter/dist/sshpre.css'
import TInputSelect from "@/Components/Form/Inputs/TInputSelect";
import TBadge from "@/Components/Badge/TBadge";
import TInputFile from "@/Components/Form/Inputs/TInputFile";
import TCheckIcon from "@/Components/Icon/TCheckIcon";
import TXIcon from "@/Components/Icon/TXIcon";

export default {
  name: "Importar",
  components: {
    TBadge,TInputSelect,TCheckIcon,TFormContent, TInputGroup, TInputText,
    AppLayout, TButton, TFormSection,TInputTextArea,TInputRadioButton, TInputFile,TXIcon
  },
  props: ['corretoras','carteiras'],
  data() {
    return {
      loading: false,
      form: this.$inertia.form({
        _method: 'POST',
        corretora_id: null,
        carteira_id: null,
        tipo: null,
        upload: null,
      }),
      status: [
        {name: 'Passive', value: 0, icon: 'XIcon', class: 'w-5 h-5 text-red-500 mr-2'},
        {name: 'Active', value: 1, icon: 'Checked', class: 'w-5 h-5 text-green-500 mr-2'}
      ],

    };
  },
  methods: {
    reset: function () {
      this.form.corretora_id = null;
      this.form.carteira_id = null;
      this.form.tipo = null;
      this.upload = null;
    },
    save() {
      this.form.post(route('carteiras.enviar'), {
        errorBag: 'customer',
        preserveScroll: true,
      });
      this.loading = true;
    }
  }
}
</script>

<style scoped>

</style>



























