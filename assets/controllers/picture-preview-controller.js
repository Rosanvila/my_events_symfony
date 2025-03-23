import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  connect() {
    console.log("Picture Preview Controller connected");
    this.element.addEventListener("picture:changed", (event) => {
      console.log("Picture changed event received", event.detail);
      this.updatePicturePreview(event.detail.base64);
    });
  }

  updatePicturePreview(base64) {
    console.log("Updating picture preview");
    const previewElement = document.getElementById("picturePreview");
    if (!previewElement) {
      console.error("Preview element not found");
      return;
    }
    if (base64) {
      console.log("Setting new image");
      previewElement.src = "data:image/jpeg;base64," + base64;
    } else {
      console.log("Clearing image");
      previewElement.src = "";
    }
  }
}
