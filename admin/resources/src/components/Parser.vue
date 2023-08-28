<template>
    <div class="parser-panel">
        <button :disabled="isLoad" @click="parse(false)" class="btn btn__green">Парсить</button>
        <button v-if="showContinueBtn" :disabled="isLoad" @click="parse(true)" class="btn btn__green">Продолжить</button>
        <button @click="stopParse()" class="btn btn__red">Завершить</button>
        <div class="message">{{message}}</div>
    </div>
</template>

<script>
import {ref, onBeforeMount} from "vue";
import http from "@/classes/http";

export default {
    name: "ParserComponent",

    setup() {
        const message = ref('');
        const isLoad = ref(false);
        const showContinueBtn = ref(false);
        const intervalTimer = 5000;
        let lastStatus = 0;
        let interval = null;

        const parse = (isContinue = false) => {
            if (!message.value) {
                message.value = 'Запущено';
            }
            let step = 0;

            if (isContinue) {
                step = lastStatus;
            }
            isLoad.value = true;
            http.parse(step)
            .catch(e => {
                message.value = e.message;
                stop();
            })

            if (!interval) {
                start();
            }
        }

        const start = () => {
            isLoad.value = true;
            interval = setInterval(() => {
                status();
            }, intervalTimer)
        }

        const stop = () => {
            isLoad.value = false;
            clearInterval(interval);
            interval = null;
        }

        const status = () => {
            http.getStatus()
            .then(res => {
                checkStatus(res.data.data);
            })
            .catch(e => {
                message.value = e.message;
                stop();
            })
        }

        const stopParse = async () => {
            const res = await http.stop();

            if (res.data.success === true) {
                message.value = 'Процесс остановлен';
                showContinueBtn.value = true;
                stop();
            }
        }

        const checkStatus = (data) => {
            const status = data.status;
            if (status) {
                lastStatus = status;
                message.value = data.message;

                if (data.error) {
                    isLoad.value = false;
                    showContinueBtn.value = true;
                    stop();
                }
                else {
                    if (!interval) {
                        start();
                    }
                }
            }
            else {
                isLoad.value = false;
            }
        }

        onBeforeMount(async () => {
            try {
                isLoad.value = true;
                const { data } = await http.getStatus();
                checkStatus(data.data);
            } catch (e) {
                message.value = e.message;
            }
        });

        return {
            message,
            isLoad,
            showContinueBtn,
            parse,
            status,
            stopParse
        }
    }
}
</script>

<style scoped>

</style>
