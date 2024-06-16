package vue;

import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.layout.GridPane;

public class CtrlAffichageSalle {

    @FXML
    private GridPane gridSalle;

    private Label[][] labels;

    @FXML
    private Label lblSx1y1;
    @FXML
    private Label lblSx1y2;
    @FXML
    private Label lblSx1y3;
    @FXML
    private Label lblSx1y4;
    @FXML
    private Label lblSx2y1;
    @FXML
    private Label lblSx2y2;
    @FXML
    private Label lblSx2y3;
    @FXML
    private Label lblSx2y4;
    @FXML
    private Label lblSx3y1;
    @FXML
    private Label lblSx3y2;
    @FXML
    private Label lblSx3y3;
    @FXML
    private Label lblSx3y4;
    @FXML
    private Label lblSx4y1;
    @FXML
    private Label lblSx4y2;
    @FXML
    private Label lblSx4y3;
    @FXML
    private Label lblSx4y4;

    @FXML
    public void initialize() {
        // Initialiser le tableau de labels
        labels = new Label[][] {
            {lblSx1y1, lblSx1y2, lblSx1y3, lblSx1y4},
            {lblSx2y1, lblSx2y2, lblSx2y3, lblSx2y4},
            {lblSx3y1, lblSx3y2, lblSx3y3, lblSx3y4},
            {lblSx4y1, lblSx4y2, lblSx4y3, lblSx4y4}
        };
    }

    // Méthode pour définir le texte d'un label spécifique
    public void setLabelText(int row, int col, String text) {
        if (row >= 1 && row <= 4 && col >= 1 && col <= 4) {
            labels[row-1][col-1].setText(text);
        } else {
            throw new IllegalArgumentException("Indices hors limites.");
        }
    }
}
