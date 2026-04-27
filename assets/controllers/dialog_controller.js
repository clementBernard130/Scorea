import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal'];

    open(event) {
        this.modalTarget.showModal();
    }

    close(event) {
        event.preventDefault();
        this.modalTarget.close();
    }

    clickOutside(event) {
        const rect = this.modalTarget.getBoundingClientRect();
        if (
            event.clientX < rect.left || event.clientX > rect.right ||
            event.clientY < rect.top || event.clientY > rect.bottom
        ) {
            this.modalTarget.close();
        }
    }
}