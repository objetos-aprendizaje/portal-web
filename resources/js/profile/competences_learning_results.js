import InfiniteTree from "infinite-tree";
import renderer from "../renderer_infinite_tree.js";
import { apiFetch } from "../app";

let treeCompetencesLearningResults;
let selectedLearningResults = new Set();

document.addEventListener("DOMContentLoaded", function () {
    instanceTreeCompetences();
    selectLearningResultsUser();
    initHandlers();
});

function initHandlers() {
    document
        .getElementById("save-competences-learning-results-btn")
        .addEventListener("click", saveLearningResults);
}

// Carga los resultados de aprendizaje seleccionados por el usuario que se encuenta en la BD
function selectLearningResultsUser() {
    function openNode(node) {
        if (node.id) {
            treeCompetencesLearningResults.openNode(node);
            openNode(node.parent);
        }
    }

    window.learningResultsUserSelected.forEach((node) => {
        selectedLearningResults.add(node);
        const n = treeCompetencesLearningResults.getNodeById(node);
        if (n) {
            openNode(n);
            treeCompetencesLearningResults.checkNode(n, true);
        }
    });

    updateSelectedLearningResults(window.learningResultsUserSelected, true);
}

function instanceTreeCompetences() {

    // Actualiza el estado indeterminate de los checkboxes de todos los nodos
    const updateCheckboxState = (treeCompetencesLearningResults) => {
        const checkboxes =
            treeCompetencesLearningResults.contentElement.querySelectorAll(
                'input[type="checkbox"]'
            );

        // Si el bloque está deshabilitado, deshabilitamos todos los checkboxes
        for (let i = 0; i < checkboxes.length; ++i) {
            const checkbox = checkboxes[i];
            if (checkbox.hasAttribute("data-indeterminate")) {
                checkbox.indeterminate = true;
            } else {
                checkbox.indeterminate = false;
            }
        }
    };

    // Instancia del nodo
    treeCompetencesLearningResults = new InfiniteTree(
        document.getElementById("tree-competences-learning-results"),
        {
            rowRenderer: renderer,
            togglerClass: "infinite-tree-toggler-svg",
            shouldSelectNode: (node) => {
                return false;
            },
            noDataText: "No hay ningún marco de competencias",
        }
    );

    // Click en cada checkbox de noto
    treeCompetencesLearningResults.on("click", (event) => {
        const currentNode = treeCompetencesLearningResults.getNodeFromPoint(
            event.clientX,
            event.clientY
        );
        if (!currentNode || event.target.className !== "checkbox") return;
        event.stopPropagation();
        treeCompetencesLearningResults.checkNode(currentNode);

        // Actualizamos los resultados de aprendizaje seleccionados
        updateSelectedCompetencesAndLearningResults(currentNode);
    });

    treeCompetencesLearningResults.on("contentDidUpdate", () => {
        updateCheckboxState(treeCompetencesLearningResults);
    });

    treeCompetencesLearningResults.on("clusterDidChange", () => {
        updateCheckboxState(treeCompetencesLearningResults);
    });

    let competencesLearningResultsCopy = JSON.parse(
        JSON.stringify(window.competencesLearningResults)
    );

    treeCompetencesLearningResults.loadData(competencesLearningResultsCopy);
}

/**
 *
 * @param {*} currentNode
 * Actualiza el set de resultados de aprendizaje seleccionados cada vez que se marca uno
 */
function updateSelectedCompetencesAndLearningResults(currentNode) {
    function getChildNodesLearningResults(node, resultSet = new Set()) {
        if (!node.children.length) return resultSet;

        node.children.forEach((child) => {
            if (child.type === "learningResult") {
                resultSet.add(child.id);
            }
            getChildNodesLearningResults(child, resultSet);
        });

        return resultSet;
    }

    const isSelected = currentNode.state.checked;

    const childLearningResults = getChildNodesLearningResults(currentNode);
    updateSelectedLearningResults(childLearningResults, isSelected);

    if (currentNode.type === "learningResult") {
        updateSelectedLearningResults([currentNode.id], isSelected);
    }
}

// Actualiza el set de resultados de aprendizaje seleccionados y el contador
function updateSelectedLearningResults(learningResults, isSelected) {
    if (!selectedLearningResults instanceof Set) {
        selectedLearningResults = new Set(selectedLearningResults);
    }

    if (isSelected) {
        learningResults.forEach((lr) => selectedLearningResults.add(lr));
    } else {
        learningResults.forEach((lr) => selectedLearningResults.delete(lr));
    }

    updateLearningResultsSelectedCounter();
}

// Actualiza el contador de resultados de aprendizaje seleccionados
function updateLearningResultsSelectedCounter() {
    document.getElementById("selected-learning-results").classList.remove("hidden");
    document.getElementById("selected-learning-results-count").innerText =
        selectedLearningResults.size;
}

function saveLearningResults() {
    const params = {
        url: "/profile/competences_learning_results/save_learning_results",
        method: "POST",
        stringify: true,
        loader: true,
        body: {
            learningResults: Array.from(selectedLearningResults),
        },
        toast: true,
    };

    apiFetch(params);
}


