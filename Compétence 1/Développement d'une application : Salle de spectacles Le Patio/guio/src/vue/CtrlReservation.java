package vue;

import controlleur.Main;
import java.util.List;
import java.util.stream.Collectors;
import javafx.beans.binding.Bindings;
import javafx.beans.binding.ObjectBinding;
import javafx.beans.property.StringProperty;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TableView;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TextField;
import javafx.scene.input.KeyEvent;
import modele.Billet;
import modele.Client;
import modele.Fauteuil;
import modele.Representation;
import modele.Reservation;

public class CtrlReservation {
    @FXML
    private TextField txtVille;

    @FXML
    private TextField txtNum;

    @FXML
    private Label labelVille;

    @FXML
    private Label labelConfirmation;

    @FXML
    private TableView<Reservation> listeReservation;
    
    @FXML
    private TableColumn<Reservation, String> colNum;

    @FXML
    private TableColumn<Reservation, String> colNom;

    @FXML
    private TableColumn<Reservation, String> colVille;

    @FXML
    private Label labelDate;

    @FXML
    private TableView<Billet> listeBillet;
    
    @FXML
    private TableColumn<Billet, String> colBilletNum;

    @FXML
    private TableColumn<Billet, String> colBilletRangee;
    
    @FXML
    private TableColumn<Billet, String> colBilletFauteuil;
    
    @FXML
    private Label labelNum;

    @FXML
    private TextField txtNom;

    @FXML
    private Label labelNom;

    private ObservableList<Reservation> reservations = FXCollections.observableArrayList();

   public CtrlReservation() {
   }

   public void setReservations() {
     Client client1 = new Client("Chaplais", "Ethan", "Rennes", "non", "0123456789", "ethanchaplais@gmail.com", "1");
     Client client2 = new Client("Nevot", "Pierre", "Guinguamp", "non", "0123456789", "pierrenevot@gmail.com", "1");
     
     Representation representation1 = new Representation("22", "20:00", false);
     
     Reservation reservation1 = new Reservation("1", "date", "autre date", client1, representation1);
     Reservation reservation2 = new Reservation("2", "date", "autre date", client2, representation1);
     
     Fauteuil fauteuil1 = new Fauteuil("3", "2");
     
     Billet billet1 = new Billet("1", client1, fauteuil1, representation1);
     
     reservation1.ajouterBillet(billet1);
     
     reservations.add(reservation1);
     reservations.add(reservation2);

     listeReservation.setItems(reservations);
   }
   

   @FXML
   void deleteNum() {
      char[] var4;
      int var3 = (var4 = this.txtNum.getText().toCharArray()).length;

      for(int var2 = 0; var2 < var3; ++var2) {
         char c = var4[var2];
         if (!Character.isDigit(c)) {
            this.txtNum.deletePreviousChar();
         }
      }

   }

   @FXML
   void imprimer() throws Exception {
      Main.impressionBillet(this.listeBillet.getItems());
   }

   @FXML
   void retour() {
      this.listeBillet.getItems().clear();
      this.listeReservation.getItems().clear();
   }

   @FXML
   void rechercheNum(KeyEvent event) {
       String num = txtNum.getText();
       List<Reservation> result = reservations.stream()
           .filter(reservation -> reservation.getNumero().contains(num))
           .collect(Collectors.toList());
       listeReservation.setItems(FXCollections.observableArrayList(result));
   }

   @FXML
   void rechercheNomVille(KeyEvent event) {
       String nom = txtNom.getText().toLowerCase();
       String ville = txtVille.getText().toLowerCase();
       List<Reservation> result = reservations.stream()
           .filter(reservation -> reservation.getClient().getNom().toLowerCase().contains(nom) &&
                                  reservation.getClient().getVille().toLowerCase().contains(ville))
           .collect(Collectors.toList());
       listeReservation.setItems(FXCollections.observableArrayList(result));
   }
   
   @FXML
   void afficheRepresentation() {
	    listeReservation.getSelectionModel().selectedItemProperty().addListener((observable, oldValue, newValue) -> {
	        if (newValue != null) {
	            labelNom.textProperty().set(newValue.getClient().getNom());
	            labelVille.textProperty().set(newValue.getClient().getVille());
	            labelNum.textProperty().set(newValue.getNumero());
	            labelDate.textProperty().set(newValue.getDate().toString());
	            labelConfirmation.textProperty().set(newValue.getDateEnvoiConf().toString());
	            
	            listeBillet.setItems(FXCollections.observableArrayList(newValue.getBillets()));
	        } else {
	            labelNom.setText("");
	            labelVille.setText("");
	            labelNum.setText("");
	            labelDate.setText("");
	            labelConfirmation.setText("");
	            
	            listeBillet.setItems(FXCollections.emptyObservableList());
	        }
	    });
	}
   
   public void initialize() {
      colNum.setCellValueFactory(cellData -> cellData.getValue().numeroProperty());
	  colNom.setCellValueFactory(cellData -> cellData.getValue().getClient().nomProperty());
	  colVille.setCellValueFactory(cellData -> cellData.getValue().getClient().villeProperty());
	     
	  colBilletNum.setCellValueFactory(cellData -> cellData.getValue().numeroProperty());
	  colBilletRangee.setCellValueFactory(cellData -> cellData.getValue().getFauteuil().rangeeProperty());
	  colBilletFauteuil.setCellValueFactory(cellData -> cellData.getValue().getFauteuil().numeroProperty());
	     
	  afficheRepresentation();
   }
}