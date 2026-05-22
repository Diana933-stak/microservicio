const modal = {
  element: document.getElementById('sprintModal'),
  title: document.getElementById('modalTitle'),
  open(title) {
    this.title.textContent = title;
    this.element.showModal();
  },
  close() {
    this.element.close();
  }
};