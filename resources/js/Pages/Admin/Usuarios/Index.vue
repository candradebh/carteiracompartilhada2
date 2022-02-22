<template>
    <app-layout>
        <!--Header-->
        <template #header>
            Usuários
        </template>
        <!--Subheader-->
        <template #subHeader>
            Personalise sua tabela
        </template>
        <!--Content-->
        <template #default>
            <t-component-color-selector @selected-color="tableColor = $event"/>

            <t-table :color="tableColor" :content="users" :header="header" :pagination="true"
                     :searchable="['name','email']">
                <template #search>
                    <grid-section :col="12" :gap="2">
                        <!--Name-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Name">
                            <t-input-text id="name"/>
                        </t-input-group>
                        <!--Email-->
                        <t-input-group class="col-span-12 md:col-span-6" label="Email">
                            <t-input-text id="email"/>
                        </t-input-group>
                    </grid-section>
                </template>
                <template #right>
                    <t-button :link="route('form-structure')" :radius="8">
                        <t-user-circle-icon class="w-6 h-6"/>
                        Novo Usuário
                    </t-button>
                </template>
                <template #photo="{props}">
                        <t-avatar :link="props.photo" :radius="8" :size="3"/>
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
    name: "Users",
    components: {
        TComponentColorSelector,
        TUserCircleIcon, TButton, GridSection, TInputText, TInputGroup, AppLayout, TTable, TAvatar, SshPre},
    props: ['users'],
    data() {
        return {
            tableColor: 'solid-blue',
            header: [
                {label: 'Avatar', key: 'photo', align: 'center', width: '5'},
                {label: 'Name', key: 'name', align: 'left'},
                {label: 'Email', key: 'email', align: 'left'}
            ],
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
