<template>
    <div class="app">
        <h2>Парсер krasnodar.f-trade.ru</h2>
        <div class="tabs">
            <span
                v-for="tab in tabs"
                @click="currentTab = tab.id"
                :class="{ active: currentTab === tab.id }"
                :key="tab.id"
            >
                {{ tab.name }}
            </span>
        </div>
        <div class="tab_content">
            <div class="tab__item" v-for="tab in tabs" :key="tab.id">
                <div v-if="currentTab === tab.id">
                    <component :is="tab.component"></component>
                </div>
            </div>
        </div>
        <Log />
    </div>
</template>

<script>
import {ref} from "vue";
import xpathSettings from "@/components/xpathSettings";
import Parser from "@/components/Parser";
import Settings from "@/components/Settings";
import Log from "@/components/Log";

export default {
    name: "AppVue",
    components: {
        xpathSettings,
        Parser,
        Settings,
        Log
    },

    setup() {
        const currentTab = ref(0);
        const tabs = [
            {
                id: 0,
                name: 'Основное',
                component: Parser,
            },
            {
                id: 1,
                name: 'Настройки',
                component: Settings,
            },
            {
                id: 2,
                name: 'Настройки XPath',
                component: xpathSettings,
            },
        ]

        return {
            currentTab,
            tabs,
        }
    }
}
</script>

<style scoped lang="sass">

</style>
