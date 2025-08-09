// assets/controllers/quote_controller.js

export default class extends Controller {
    static targets = ["loadingAssistance", "loadingPersons", "unloadingAssistance", "unloadingPersons"];

    connect() {
        //console.log('Stimulus controller "quote" připojen');
        this.toggleFields();
    }

    toggleFields() {
        // Logika pro loadingAddress
        const isLoadingAssistanceChecked = this.loadingAssistanceTarget.checked;
        if (isLoadingAssistanceChecked) {
            this.show(this.loadingPersonsTarget);
        } else {
            this.hide(this.loadingPersonsTarget);
        }

        // Logika pro unloadingAddress
        const isUnloadingAssistanceChecked = this.unloadingAssistanceTarget.checked;
        if (isUnloadingAssistanceChecked) {
            this.show(this.unloadingPersonsTarget);
        } else {
            this.hide(this.unloadingPersonsTarget);
        }
    }

    show(element) {
        element.style.display = 'block';
    }

    hide(element) {
        element.style.display = 'none';
        // Volitelně: pokud pole skryješ, můžeš ho vyprázdnit
        // element.querySelector('input').value = '';
    }
}
