import { Modal, Dropdown } from 'bootstrap';

// Initialisation de la modal si nécessaire
document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('modalLogin');
    if (modalElement) {
        const modal = new Modal(modalElement);
    }
});