import { apiFetch, updateInputFile } from "./app.js";

document.addEventListener("DOMContentLoaded", function () {
    updateInputFile();

    document
        .getElementById("inscribe-learning-object-btn")
        .addEventListener("click", function () {
            let learningObjectType = this.dataset.learning_object_type;
            let learningObjectUid = this.dataset.learning_object_uid;

            inscribeCourse(learningObjectType, learningObjectUid);
        });
});

function inscribeCourse(learningObjectType, learningObjectUid) {
    const params = {
        url: "/cart/inscribe",
        method: "POST",
        body: {
            learningObjectType,
            learningObjectUid,
        },
        stringify: true,
        toast: true,
        loader: true,
    };

    apiFetch(params)
        .then((response) => {
            let redirectUrl = "#";
            if (learningObjectType === "course") {
                if (response.statusWithLearningObject === "INSCRIBED")
                    redirectUrl = "/profile/my_courses/inscribed";
                else if (response.statusWithLearningObject === "ENROLLED")
                    redirectUrl = "/profile/my_courses/enrolled";
            } else if (learningObjectType === "educational_program") {
                if (response.statusWithLearningObject === "INSCRIBED")
                    redirectUrl = "/profile/my_educational_programs/inscribed";
                else if (response.statusWithLearningObject === "ENROLLED")
                    redirectUrl = "/profile/my_educational_programs/enrolled";
            }

            window.location.href = redirectUrl;
        })
        .catch((error) => {
            console.error("Error:", error);
        });
}
