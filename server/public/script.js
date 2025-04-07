document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contactForm");
    const contactsList = document.getElementById("contactsList");

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = {
            name: document.getElementById("name").value,
            email: document.getElementById("email").value,
            message: document.getElementById("message").value
        };

        try {
            const response = await fetch("http://127.0.0.1:8080/data", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            alert(result.message || "Error en el servidor");
            loadContacts();
        } catch (error) {
            console.error("Error enviando contacto:", error);
            alert("Hubo un error al enviar el contacto.");
        }
    });

    async function loadContacts() {
        try {
            const response = await fetch("http://127.0.0.1:8080/data");
            const contacts = await response.json();

            contactsList.innerHTML = "";
            contacts.forEach(contact => {
                const li = document.createElement("li");
                li.innerHTML = `
                    <div class="p-2 bg-gray-200 rounded mt-2 flex justify-between">
                        <span>${contact.name} - ${contact.email} - ${contact.message}</span>
                        <button class="bg-red-500 text-white px-2 rounded delete-btn" data-id="${contact.id}">X</button>
                    </div>
                `;
                contactsList.appendChild(li);
            });

            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", async function () {
                    const contactId = this.getAttribute("data-id");
                    await deleteContact(contactId);
                });
            });

        } catch (error) {
            console.error("Error cargando contactos:", error);
        }
    }

    async function deleteContact(id) {
        try {
            const response = await fetch(`http://127.0.0.1:8080/data/${id}`, { method: "DELETE" });
            const result = await response.json();
            alert(result.message);
            loadContacts(); // Recargar lista después de eliminar
        } catch (error) {
            console.error("Error eliminando contacto:", error);
        }
    }

    loadContacts();
});
