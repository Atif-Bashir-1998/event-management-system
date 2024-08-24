import { FunctionalComponent, HTMLAttributes } from "vue";

export interface SidebarMenuItem {
    route: string,
    label: string,
    icon: FunctionalComponent<HTMLAttributes>
}
