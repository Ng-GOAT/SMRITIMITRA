const darkModeToggle =
    document.getElementById("darkModeToggle");

const largeTextToggle =
    document.getElementById("largeTextToggle");


if (darkModeToggle) {

    darkModeToggle.addEventListener("change", function () {

        document.body.classList.toggle(
            "dark-mode",
            this.checked
        );

    });

}


if (largeTextToggle) {

    largeTextToggle.addEventListener(
        "change",
        function () {

            document.body.classList.toggle(
                "large-text",
                this.checked
            );

        }
    );

}