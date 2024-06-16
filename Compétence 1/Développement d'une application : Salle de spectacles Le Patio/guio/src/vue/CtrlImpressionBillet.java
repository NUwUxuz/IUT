package vue;

import javafx.fxml.FXML;
import javafx.scene.control.TableView;
import javafx.scene.control.TableColumn;
import modele.Billet;
import javafx.event.ActionEvent;

import java.util.List;

public class CtrlImpressionBillet {

    @FXML
    private TableView<Billet> lvImprime;
    
    @FXML
    private TableColumn<Billet, String> colImrpimeNum;
    
    @FXML
    private TableColumn<Billet, String> colImrpimeNom;
    
    @FXML
    private TableColumn<Billet, String> colImrpimeJour;
    
    @FXML
    private TableColumn<Billet, String> colImrpimeHeure;
    
    @FXML
    private TableView<Billet> lvImprimable;
    
    @FXML
    private TableColumn<Billet, String> colImrpimableNum;
    
    @FXML
    private TableColumn<Billet, String> colImrpimableNom;
    
    @FXML
    private TableColumn<Billet, String> colImrpimableJour;
    
    @FXML
    private TableColumn<Billet, String> colImrpimableHeure;

    private List<Billet> billets;

    public void setBillets(List<Billet> billets) {
        this.billets = billets;
        lvImprimable.getItems().addAll(billets);
    }

    @FXML
    public void initialize() {
    	colImrpimeNum.setCellValueFactory(cellData -> cellData.getValue().numeroProperty());
    	colImrpimeNom.setCellValueFactory(cellData -> cellData.getValue().getClient().nomProperty());
    	colImrpimeJour.setCellValueFactory(cellData -> cellData.getValue().getRepresentation().jourProperty());
    	colImrpimeHeure.setCellValueFactory(cellData -> cellData.getValue().getRepresentation().heureProperty());
    	
    	colImrpimableNum.setCellValueFactory(cellData -> cellData.getValue().numeroProperty());
    	colImrpimableNom.setCellValueFactory(cellData -> cellData.getValue().getClient().nomProperty());
    	colImrpimableJour.setCellValueFactory(cellData -> cellData.getValue().getRepresentation().jourProperty());
    	colImrpimableHeure.setCellValueFactory(cellData -> cellData.getValue().getRepresentation().heureProperty());
    }

    @FXML
    private void imprime(ActionEvent event) {
        // print dans la console les villets dans lvImprime
        lvImprime.getItems().forEach(System.out::println);
    }

    @FXML
    private void annuler (ActionEvent event) {
        // Ferme la fenêtre
        lvImprime.getItems().clear();
        lvImprimable.getItems().clear();
    }

    // Drag&Drop de lvImprime à lvImprimable et vice-versa
    void imprimeToImprimable(ActionEvent event) {
        Billet b = lvImprime.getSelectionModel().getSelectedItem();
        if (b != null) {
            lvImprimable.getItems().add(b);
            lvImprime.getItems().remove(b);
        }
    }

    void imprimableToImprime(ActionEvent event) {
        Billet b = lvImprimable.getSelectionModel().getSelectedItem();
        if (b != null) {
            lvImprime.getItems().add(b);
            lvImprimable.getItems().remove(b);
        }
    }
}