import {
    apiFetch,
    formatDateLong,
    updatePagination,
    handlePagination,
    updateInputFile,
    handleTabs,
} from "../app.js";
import { hideModal, showModal } from "../modal_handler.js";

let courseDocuments = [];
document.addEventListener("DOMContentLoaded", function () {
    getInscribedCourses();
    getEnrolledCourses();
    handlePaginationMyCourses();

    document
        .getElementById("search-course-btn")
        .addEventListener("click", function () {
            handleSearchCourses();
        });

    document
        .getElementById("search-course-input")
        .addEventListener("keyup", function (event) {
            if (event.key === "Enter") handleSearchCourses();
        });

    document
        .getElementById("upload-documents-form")
        .addEventListener("submit", saveDocumentsCourse);

    updateInputFile();

    document.body.addEventListener("click", function (event) {
        const classClicked = event.target.classList;

        if (classClicked.contains("documentation-btn")) {
            var courseUid = event.target
                .closest(".course-block")
                .querySelector(".course-uid").value;
            loadUploadFilesModal(courseUid);
        } else if (classClicked.contains("download-document")) {
            const documentUid = event.target.dataset.document_uid;
            downloadDocumentCourse(documentUid);
        } else if (classClicked.contains("upload-files")) {
            const courseUid = event.target.dataset.course_uid;
            loadUploadFilesModal(courseUid);
        } else if (classClicked.contains("enroll-course-btn")) {
            const courseUid = event.target.dataset.course_uid;
            enrollCourse(courseUid);
        }
    });

    handleTabs();
});

function downloadDocumentCourse(documentCourseUid) {
    console.log(documentCourseUid);
    const params = {
        method: "POST",
        url: "/profile/my_courses/download_document_course",
        body: {
            course_document_uid: documentCourseUid,
        },
        stringify: true,
        download: true,
        loader: true,
    };

    apiFetch(params).then();
}

function saveDocumentsCourse() {
    const formData = new FormData(this);

    const params = {
        method: "POST",
        url: "/profile/my_courses/save_documents_course",
        body: formData,
        loader: true,
    };

    apiFetch(params).then((response) => {
        console.log(response);
        hideModal("upload-documents-modal");
        getInscribedCourses();
    });
}

function loadUploadFilesModal(courseUid) {
    const documentsCourse = courseDocuments.filter(
        (document) => document.course_uid == courseUid
    );

    const documentTemplate = document.getElementById("document-template");

    const documentContainer = document.getElementById("documents-container");

    documentContainer.innerHTML = "";

    document.getElementById("documents-modal-course-uid").value = courseUid;

    documentsCourse.forEach((documentCourse) => {
        let templateCloned = documentTemplate.content.cloneNode(true);

        let templateFilled = fillDocumentTemplate(
            templateCloned,
            documentCourse
        );

        documentContainer.appendChild(
            document.importNode(templateFilled, true)
        );
    });

    showModal("upload-documents-modal", "Subir archivos");
}

function handleSearchCourses() {
    const search = document.getElementById("search-course-input").value;
    getInscribedCourses(1, 3, search);
}

function handlePaginationMyCourses() {
    const containerInscribedCoursesPagination = document.getElementById(
        "pagination-inscribed-courses"
    );

    handlePagination(containerInscribedCoursesPagination, (pageNumber) => {
        getInscribedCourses(pageNumber);
    });

    const containerEnrolledCoursesPagination = document.getElementById(
        "pagination-enrolled-courses"
    );

    handlePagination(containerEnrolledCoursesPagination, (pageNumber) => {
        getEnrolledCourses(pageNumber);
    });
}

function getInscribedCourses(page = 1, items_per_page = 3, search = null) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/get_courses",
        body: {
            page,
            items_per_page,
            search,
            status: "INSCRIBED",
        },
        stringify: true,
        loader: true,
    };

    apiFetch(params).then((response) => {
        const containerInscribedCoursesPagination = document.getElementById(
            "pagination-inscribed-courses"
        );

        updatePagination(
            containerInscribedCoursesPagination,
            response.current_page,
            response.last_page
        );

        const courses = response.data;

        let coursesInscribedContainer = document.getElementById(
            "courses-inscribed-container"
        );

        let noCoursesInscribed = document.getElementById(
            "no-courses-inscribed"
        );

        if (courses.length) {
            coursesInscribedContainer.classList.remove("hidden");
            noCoursesInscribed.classList.add("hidden");
            const listCoursesInscribed = document.getElementById(
                "courses-inscribed-list"
            );
            loadCourses(response.data, listCoursesInscribed, "INSCRIBED");
        } else {
            coursesInscribedContainer.classList.add("hidden");
            noCoursesInscribed.classList.remove("hidden");
        }
    });
}

/**
 * Obtiene los cursos en los que está matriculado
 * @param {*} page
 * @param {*} items_per_page
 * @param {*} search
 */
function getEnrolledCourses(page = 1, items_per_page = 1, search = null) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/get_courses",
        body: {
            page,
            items_per_page,
            search,
            status: "ENROLLED",
        },
        stringify: true,
        loader: true,
    };

    apiFetch(params).then((response) => {
        const containerPagination = document.getElementById(
            "pagination-enrolled-courses"
        );

        updatePagination(
            containerPagination,
            response.current_page,
            response.last_page
        );

        const listCoursesEnrolled = document.getElementById(
            "courses-enrolled-list"
        );

        const enrolledCoursesContainer = document.getElementById(
            "courses-enrolled-container"
        );
        const coursesEnrolled = response.data;

        const noEnrolledCourses = document.getElementById(
            "no-courses-enrolled"
        );

        if (coursesEnrolled.length) {
            enrolledCoursesContainer.classList.remove("hidden");
            noEnrolledCourses.classList.add("hidden");
            loadCourses(response.data, listCoursesEnrolled, "ENROLLED");
        } else {
            enrolledCoursesContainer.classList.add("hidden");
            noEnrolledCourses.classList.remove("hidden");
        }
    });
}

function loadCourses(courses, container, status) {
    let courseTemplate = document.getElementById("course-template");

    let noCoursesFound = document.getElementById("no-courses-found");

    container.innerHTML = "";

    //courseDocuments = [];

    if (courses.length) {
        noCoursesFound.classList.add("hidden");
        courses.forEach((course) => {
            let templateCourseCloned = courseTemplate.content.cloneNode(true);

            if (status === "INSCRIBED") {
                fillCourseInscribedTemplate(templateCourseCloned, course);
            } else if (status === "ENROLLED") {
                fillCourseEnrolledTemplate(templateCourseCloned, course);
            }

            fillCourseTemplate(templateCourseCloned, course);
            container.appendChild(
                document.importNode(templateCourseCloned, true)
            );

            // Concatenamos al array de documentos pendientes de subir
            courseDocuments = [...courseDocuments, ...course.course_documents];
        });
    } else {
        noCoursesFound.classList.remove("hidden");
    }
}

function enrollCourse(courseUid) {
    const params = {
        method: "POST",
        url: "/profile/my_courses/enroll_course",
        body: {
            course_uid: courseUid,
        },
        stringify: true,
        loader: true,
        toast: true,
    };

    apiFetch(params).then(() => {
        getEnrolledCourses();
        getInscribedCourses();
    });
}

function fillCourseEnrolledTemplate(template, course) {
    template.querySelector(".inscription-date").classList.add("hidden");

    let realizationStartDate = new Date(course.realization_start_date);
    let actualDate = new Date();

    template.querySelector(".enrolling-date").classList.add("hidden");

    if (realizationStartDate > actualDate) {
        template
            .querySelector(".btn-action-course")
            .classList.add("btn-blocked");
        template.querySelector(".indicator").classList.add("pending");
        template.querySelector(".indicator-label").innerHTML =
            "Pendiente realización";
    } else {
        template
            .querySelector(".btn-action-course")
            .classList.add("upload-files");
        template.querySelector(".indicator").classList.add("openned");
        template.querySelector(".indicator-label").innerHTML =
            "Listo para realizar";
    }
}

function fillCourseInscribedTemplate(template, course) {
    template.querySelector(".btn-action-course").innerHTML = "Matricularse";

    let enrollingStartDate = new Date(course.enrolling_start_date);
    let actualDate = new Date();

    if (enrollingStartDate > actualDate) {
        template
            .querySelector(".btn-action-course")
            .classList.add("btn-blocked");
        template.querySelector(".indicator").classList.add("pending");
        template.querySelector(".indicator-label").innerHTML =
            "Pendiente matriculación";
    } else {
        template
            .querySelector(".btn-action-course")
            .classList.add("enroll-course-btn");
        template.querySelector(".indicator").classList.add("openned");
        template.querySelector(".indicator-label").innerHTML =
            "Listo para matricular";
    }

    template.querySelector(".inscription-date").classList.add("hidden");

    template
        .querySelector(".enrolling-date")
        .querySelector(".date").innerHTML = `${formatDateLong(
        course.enrolling_start_date
    )} - ${formatDateLong(course.enrolling_finish_date)}`;
}

function fillCourseTemplate(template, course) {
    template.querySelector(".course-uid").value = course.uid;
    template.querySelector(".title").innerHTML = course.title;

    template
        .querySelector(".inscription-date")
        .querySelector(".date").innerHTML = `${formatDateLong(
        course.inscription_start_date
    )} - ${formatDateLong(course.inscription_finish_date)}`;

    template
        .querySelector(".realization-date")
        .querySelector(".date").innerHTML = `${formatDateLong(
        course.realization_start_date
    )} - ${formatDateLong(course.realization_finish_date)}`;

    template.querySelector(".btn-action-course").dataset.course_uid =
        course.uid;

    let documentationBtn = template.querySelector(".documentation-btn");

    if (!course.course_documents.length) {
        template
            .querySelector(".documentation-btn-container")
            .classList.add("hidden");
    }

    documentationBtn.innerHTML = checkPendingUploadDocuments(course)
        ? "Documentación pendiente"
        : "Ver documentación";

    if (!checkPendingUploadDocuments(course)) {
        template
            .querySelector(".require-documentation")
            .classList.add("hidden");
    }

    return template;
}

function fillDocumentTemplate(template, document) {
    template.querySelector(".document-name").innerHTML = document.document_name;
    template.querySelector(".document-input").id = document.uid;
    template.querySelector(".document-input").name = document.uid;

    if (document.course_student_document) {
        template.querySelector(".download-document").classList.remove("hidden");
        template.querySelector(".download-document").dataset.document_uid =
            document.course_student_document.uid;
    }

    template.querySelector(".document-input-label").htmlFor = document.uid;
    return template;
}

function checkPendingUploadDocuments(course) {
    let pendingUploadDocuments = false;

    course.course_documents.forEach((course_document) => {
        if (!course_document.course_student_document)
            pendingUploadDocuments = true;
    });

    return pendingUploadDocuments;
}
