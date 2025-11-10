document.addEventListener("DOMContentLoaded", function () {
    // Manipulador para os botões de dropdown
    const dropdownButtons = document.querySelectorAll('.dropdown-button');

    dropdownButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.stopPropagation(); // Impede que o clique se propague

            // Fecha todos os outros dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.add('hidden');
                }
            });

            // Alterna a visibilidade do dropdown atual
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    });

    // Fecha os dropdowns ao clicar fora deles
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    });

});