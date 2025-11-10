<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".dropdown-button");

        buttons.forEach(button => {
            button.addEventListener("click", function(event) {
                event.stopPropagation();

                // Fecha todos os outros dropdowns
                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.add("hidden");
                    }
                });

                // Alterna apenas o menu clicado
                this.nextElementSibling.classList.toggle("hidden");
            });
        });

        // Fecha o dropdown ao clicar fora
        document.addEventListener("click", function() {
            document.querySelectorAll(".dropdown-menu").forEach(menu => {
                menu.classList.add("hidden");
            });
        });
    });

    function openTab(evt, tabName) {
        // Esconde todos os conteúdos das abas
        const tabcontents = document.querySelectorAll(".tabcontent");
        tabcontents.forEach((tab) => {
            tab.classList.add("hidden");
        });

        // Remove a classe "active" de todos os botões
        const tablinks = document.querySelectorAll(".tablink");
        tablinks.forEach((link) => {
            link.classList.remove("bg-blue-500", "text-white");
            link.classList.add("bg-gray-200", "text-gray-700");
        });

        // Mostra o conteúdo da aba atual e adiciona a classe "active" ao botão
        document.getElementById(tabName).classList.remove("hidden");
        evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
        evt.currentTarget.classList.add("bg-blue-500", "text-white");
    }

    // Mostra a primeira aba por padrão
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelector(".tablink").click();
    });
</script>
