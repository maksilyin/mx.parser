import axios from 'axios';

class http {
    base = '/bitrix/admin/mx_parser.php';
    parserController = 'controller=parser';
    settingsController = 'controller=settings';

    getStatus = () => {
        const url = this.base + '?'+ this.parserController +'&action=status';

        return axios.get(url);
    }

    getLog = () => {
        const url = this.base + '?'+ this.parserController +'&action=log';

        return axios.get(url);
    }

    parse = (status = false) => {
        let url = this.base + '?'+ this.parserController;
        if (status !== false) {
            url += '&step=' + status;
        }
        return axios.post(url);
    }

    stop = () => {
        const url = this.base + '?'+ this.parserController +'&action=stop';

        return axios.get(url);
    }

    getSettings = () => {
        const url = this.base + '?'+ this.settingsController;
        return axios.get(url);
    }

    getSettingsDefault = () => {
        const url = this.base + '?'+ this.settingsController + '&action=default';
        return axios.get(url);
    }

    saveSettings = (formData) => {
        const url = this.base + '?'+ this.settingsController + '&action=save';
        return axios.post(url, { settings: formData });
    }

}

export default new http();
