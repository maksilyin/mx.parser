<template>
    <div v-if="messages.length" class="log">
        <div v-for="(message, index) in messages" :key="index" class="log__item" :class="message.type">
            <span>{{ message.date }}</span> <span class="log__type">{{ message.type }}</span>: <span>{{ message.message }}</span>
        </div>
    </div>
</template>

<script>
import {onBeforeMount, ref} from "vue";
import http from "@/classes/http";

export default {
    name: "LogMessages",
    setup() {
        const messages = ref([]);
        const timer = 10000;

        const getLogs = () => {
            http.getLog()
                .then(res => {
                    if (res.data.data) {
                        messages.value = res.data.data;
                    }
                })
        }

        onBeforeMount(() => {

            getLogs();

            setInterval(() => {
                getLogs()
            }, timer)
        });

        return {
            messages
        }
    }
}
</script>

<style scoped lang="sass">
.log
    max-height: 400px
    width: 100%
    overflow-y: auto
    padding: 5px
    border: 1px solid #b0b0b0
    background-color: #f5f9f9
    box-sizing: border-box
    &__item
        font-size: 12px
        margin-bottom: 2px
        &.error
            color: #cc3030
        &.message
            color: #6daf41
            margin-top: 0
        &.warning
            color: #cf9f00
    &__type
        text-transform: uppercase
</style>
