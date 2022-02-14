<template>
  <app-layout>
    <template #breadcrumb>
      <t-breadcrumb :breadcrumbStyle="1" :breadcrumbs="breadcrumbs"/>
    </template>
    <template #header>
      Bem vindo
    </template>
    <template #subHeader>
    </template>
    <template #default>
      <!--Statistical Widgets-->
      <grid-section :col="2">
        <!--With Icon, Down Direction-->
        <t-statistic-widget
            color="solid-black"
            diff-direction="down"
            diff-value="15%"
            widget-value="254">
          <template #title>
            Taxas
          </template>
          <template #icon>
            <t-cash-icon class="w-16 h-16"/>
          </template>
        </t-statistic-widget>
        <!--With Icon, Up Direction-->
        <t-statistic-widget
            :radius="5"
            color="solid-green"
            diff-direction="up"
            diff-value="8"
            widget-value="30">
          <template #title>
            Compras
          </template>
          <template #icon>
            <t-shopping-bag-icon class="w-16 h-16"/>
          </template>
        </t-statistic-widget>
        <!--Simple-->
        <t-statistic-widget
            :radius="5"
            color="solid-red"
            diff-direction="down"
            diff-value="8%"
            widget-value="12.254,05 TL">
          <template #title>
            Vendas
          </template>
        </t-statistic-widget>
        <!--With Picture, Down Direction-->
        <t-statistic-widget
            :radius="5"
            color="solid-white"
            diff-direction="down"
            diff-value="15%"
            widget-value="254 USD">
          <template #title>
           Lucros
          </template>
          <template #picture>
            <img :src="'/img/samples/imgMelBaylon-6WLcOFn4HKE-unsplash.jpg'">
          </template>
        </t-statistic-widget>
      </grid-section>
      <!--Content Box-->
      <grid-section :col="3">
        <!--List-->
        <t-content-card :width="1">
          <template #title>
            Usuários
          </template>
          <template #content>
            <t-list :radius="3" class="w-full" color="solid-white">
              <t-list-item>
                <template #icon>
                  <t-avatar :radius="8" :size="2" src="https://i.pravatar.cc/150?u=1"></t-avatar>
                </template>
                <template #default>Sinem Aydoğan</template>
              </t-list-item>
              <t-list-item>
                <template #icon>
                  <t-avatar :radius="8" :size="2" src="https://i.pravatar.cc/150?u=2"></t-avatar>
                </template>
                <template #default>Zuhal Taşçı</template>
              </t-list-item>
              <t-list-item>
                <template #icon>
                  <t-avatar :radius="8" :size="2" src="https://i.pravatar.cc/150?u=3"></t-avatar>
                </template>
                <template #default>Ayşe Gürel</template>
              </t-list-item>
              <t-list-item>
                <template #icon>
                  <t-avatar :radius="8" :size="2" src="https://i.pravatar.cc/150?u=4"></t-avatar>
                </template>
                <template #default>Günel Deren</template>
              </t-list-item>
            </t-list>
          </template>
        </t-content-card>
        <t-content-card :width="2">
          <template #title>
            Minhas Carteiras
          </template>
          <template #content>
            <!--Table-->
            <t-table :content="carteiras" :header="tableHeader" :radius="0" :shadow="false" color="solid-white">
              <template #status="{props}">
                <div class="flex justify-center">
                  <t-progress :value="props.status" color="solid-green"/>
                </div>
              </template>
            </t-table>
          </template>
        </t-content-card>
      </grid-section>
      <!--Alerts-->
      <grid-section :col-tablet="3">
        <!--Red-Deleted-->
        <t-alert :closeable="true" color="solid-red">
          <t-trash-icon slot="icon" class="w-6 h-6"/>
          The user deleted successfully,click me
        </t-alert>
        <!--Blue-Info-->
        <t-alert color="solid-blue">
          <t-information-circle-icon slot="icon" class="w-6 h-6"/>
          The all of changes overwrite to themself
        </t-alert>
        <!--Green Success-->
        <t-alert color="solid-green">
          <t-check-circle-icon slot="icon" class="w-6 h-6"/>
          New item saved successfully
        </t-alert>
      </grid-section>
      <!--Rich Alerts-->
      <grid-section :col-tablet="2">
        <t-alert :radius="3" color="solid-white">
          <t-avatar slot="icon" :radius="8" :size="3" src="https://i.pravatar.cc/300"/>
          The new user is successfully added.
          <br/><b>User:</b> Sinan AYDOĞAN
        </t-alert>
        <t-alert :radius="3" color="light-red" :closeable="true">
          <t-avatar slot="icon" :radius="8" :size="3" src="https://i.pravatar.cc/300"/>
          The user deleted successfully
          <br/><b>User:</b> Sinan AYDOĞAN
        </t-alert>
      </grid-section>
    </template>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import Welcome from '@/Jetstream/Welcome'
import GridSection from "@/Layouts/GridSection";
import TStatisticWidget from "@/Components/Card/TStatisticWidget";
import TCashIcon from "@/Components/Icon/TCashIcon";
import TShoppingBagIcon from "@/Components/Icon/TShoppingBagIcon";
import TContentCard from "@/Components/Card/TContentCard";
import TList from "@/Components/List/TList";
import TListItem from "@/Components/List/TListItem";
import TAvatar from "@/Components/Avatar/TAvatar";
import TTable from "@/Components/Table/TTable";
import TBreadcrumb from "@/Components/Breadcrumb/TBreadcrumb";
import TProgress from "@/Components/Progress/TProgress";
import TAlert from "@/Components/Alert/TAlert";
import TInformationCircleIcon from "@/Components/Icon/TInformationCircleIcon";
import TTrashIcon from "@/Components/Icon/TTrashIcon";
import TCheckCircleIcon from "@/Components/Icon/TCheckCircleIcon";

export default {
  components: {
    TCheckCircleIcon,
    TTrashIcon,
    TInformationCircleIcon,
    TAlert,
    TProgress,
    TBreadcrumb,
    TTable,
    TAvatar,
    TListItem,
    TList,
    TContentCard,
    TShoppingBagIcon,
    TCashIcon,
    TStatisticWidget,
    GridSection,
    AppLayout,
    Welcome,
  },
  props: ['sessions','carteiras'],
  data() {
    return {
      tableHeader: [
        {key: 'id', label: 'ID', position: 'center'},
        {key: 'nome', label: 'Nome', position: 'left'},
        {key: 'tipomoeda', label: 'Moeda', position: 'left'},
        {key: 'total', label: 'Total', position: 'right'},
        {key: 'status', label: 'Status', position: 'center'}
      ],

      breadcrumbs: [
        {label: 'Home', link: '/', active: false},
        {label: 'Dashboard', link: '', active: true, activeColor: 'blue'}
      ]
    }
  }
}
</script>
