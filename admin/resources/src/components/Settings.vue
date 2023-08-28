<template>
    <div class="form__row">
        <div class="form__input form__col-2">
            <label>Инфоблок с товарами</label>
            <input type="text" v-model="formData.iblock_id">
        </div>
        <div class="form__input form__col-2">
            <label>Определять товар по ключу (article, name)</label>
            <input type="text" v-model="formData.productKey">
        </div>
        <div class="form__input form__col-2">
            <label>Уменьшать цену на %</label>
            <input type="text" v-model="formData.price_less">
        </div>
    </div>
    <div class="form__bottom">
        <button @click="setDefault()" :disabled="isLoading" class="btn btn__blue">По умолчанию</button>
        <button :disabled="isLoading" @click="save()" class="btn btn__green">Сохранить</button>
    </div>
</template>

<script>
import {onBeforeMount, reactive, ref} from "vue";
import http from "@/classes/http";

export default {
    name: "SettingsMain",

    setup() {
        const isLoading = ref(false);
        const readonly = ref(true);
        const formData = reactive( {
            iblock_id: 30,
            productKey: 'article',
            price_less: '1',
        });

        const setDefault = async () => {
            isLoading.value = true;
            const { data } = await http.getSettingsDefault();

            if (data.data) {
                setValues(data.data);
                await save();
            }
            isLoading.value = false;
        }

        const save = async () => {
            await http.saveSettings(formData);
            isLoading.value = false;
        }

        const setValues = (newValues) => {
            for (let key in formData) {
                if (newValues[key]) {
                    formData[key] = newValues[key];
                }
            }
        }

        onBeforeMount(async () => {
            isLoading.value = true;
            try {
                const { data } = await http.getSettings();

                if (data.data) {
                    setValues(data.data);
                }

            } catch (e) {
                console.log(e.message)
            }
            isLoading.value = false;
        });

        return {
            formData,
            readonly,
            isLoading,
            save,
            setDefault,
        }
    }
}
</script>

<style scoped>

</style>
