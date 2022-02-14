export const settingsMenuMixin = {
    data(){
        return {
            menuList: [
                {
                    label: "Configurações",
                    icon: "cog",
                    link: "settings",
                    linkType: "route",
                    type: "standard",
                    activeKey: ["settings-user"],
                },
                {
                    label: "Usuários",
                    icon: "",
                    link: "settings-user.index",
                    linkType: "route",
                    type: "standard",
                    activeKey: ["settings-user"],
                },
                {
                    label: "Roles",
                    icon: "",
                    link: "settings-role",
                    linkType: "route",
                    type: "standard",
                    activeKey: ["settings-role"],
                },
                {
                    label: "Permissions",
                    icon: "",
                    link: "settings-permission",
                    linkType: "route",
                    type: "standard",
                    activeKey: ["settings-permission"],
                },
                {
                    label: "System",
                    icon: "",
                    link: "settings-system",
                    linkType: "route",
                    type: "standard",
                    activeKey: ["settings-system"],
                }
            ]
        }
    }
}
