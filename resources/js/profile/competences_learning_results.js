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
    document.getElementById("save-competences-learning-results-btn").addEventListener("click", saveLearningResults);
}

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
}

function instanceTreeCompetences() {
    const updateCheckboxState = (treeCompetencesLearningResults) => {
        const checkboxes = treeCompetencesLearningResults.contentElement.querySelectorAll(
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

    treeCompetencesLearningResults.on("click", (event) => {
        const currentNode = treeCompetencesLearningResults.getNodeFromPoint(event.clientX, event.clientY);
        if (!currentNode || event.target.className !== "checkbox") return;
        event.stopPropagation();
        treeCompetencesLearningResults.checkNode(currentNode);

        // Llamada a la función con el nodo actual
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

    const { id, state } = currentNode;
    const isSelected = state.checked;

    function updateSet(set, items, add) {
        if (add) {
            set.add(id);
            items.forEach(item => set.add(item));
        } else {
            set.delete(id);
            items.forEach(item => set.delete(item));
        }
        return set;
    }

    const childLearningResults = getChildNodesLearningResults(currentNode);

    // Convertir selectedLearningResults a Set si aún no lo es
    if (!selectedLearningResults instanceof Set) {
        selectedLearningResults = new Set(selectedLearningResults);
    }

    selectedLearningResults = updateSet(
        selectedLearningResults,
        Array.from(childLearningResults),
        isSelected
    );

    if (currentNode.type === "learningResult") {
        selectedLearningResults = updateSet(selectedLearningResults, [id], isSelected);
    }

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
