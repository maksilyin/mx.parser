<template>
    <div class="form__row">
        <div class="form__input form__col-2">
            <label>Адрес</label>
            <input type="text" :readonly="readonly" v-model="formData.url">
        </div>
        <div class="form__input form__col-2">
            <label>Категория</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathCatalogLinkMenu">
        </div>
        <div class="form__input form__col-2">
            <label>Подкатегория</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathSubcategoryBlock">
        </div>
        <div class="form__input form__col-2">
            <label>Подкатегория ссылка</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathSubcategoryLink">
        </div>
        <div class="form__input form__col-2">
            <label>Подкатегория картинка</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathSubcategoryImage">
        </div>
        <div class="form__input form__col-2">
            <label>Параметр пагинации</label>
            <input type="text" :readonly="readonly" v-model="formData.paginationParam">
        </div>
        <div class="form__input form__col-2">
            <label>Карточка товара</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathCard">
        </div>
        <div class="form__input form__col-2">
            <label>Карточка товара ссылка</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathCardProductLink">
        </div>
        <div class="form__input form__col-2">
            <label>Карточка товара название</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathCardProductName">
        </div>
        <div class="form__input form__col-2">
            <label>Карточка товара артикул</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathCardProductArticle">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: хлебные крошки</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailBreadcrumbs">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: цена</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailPrice">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: характеристики</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailChar">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: характеристики строка</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailCharRow">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: характеристики ячейка</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailCharCell">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: картинка</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailImage">
        </div>
        <div class="form__input form__col-2">
            <label>Товар детально: описание</label>
            <input type="text" :readonly="readonly" v-model="formData.xpathProductDetailDescription">
        </div>
    </div>
    <div class="form__bottom">
        <button @click="setDefault()" :disabled="isLoading" class="btn btn__blue">По умолчанию</button>
        <button @click="readonly = false" :disabled="isLoading" class="btn btn__green">Изменить</button>
        <button v-if="!readonly" :disabled="isLoading" @click="save()" class="btn btn__green">Сохранить</button>
    </div>
</template>

<script>
import {onBeforeMount, reactive, ref} from "vue";
import http from "@/classes/http";

export default {
    name: "xpathSettings",

    setup() {
        const isLoading = ref(false);
        const readonly = ref(true);
        const formData = reactive( {
            url: 'https://krasnodar.f-trade.ru',
            xpathCatalogLinkMenu: '',
            xpathSubcategoryBlock: '',
            xpathSubcategoryLink: '',
            xpathSubcategoryImage: '',
            paginationParam: '',
            xpathCard: '',
            xpathCardProductLink: '',
            xpathCardProductName: '',
            xpathCardProductArticle: '',
            xpathProductDetailBreadcrumbs: '',
            xpathProductDetailPrice: '',
            xpathProductDetailChar: '',
            xpathProductDetailCharRow: '',
            xpathProductDetailCharCell: '',
            xpathProductDetailImage: '',
            xpathProductDetailDescription: '',
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
