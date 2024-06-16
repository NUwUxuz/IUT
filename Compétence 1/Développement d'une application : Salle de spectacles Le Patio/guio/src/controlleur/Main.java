package controlleur;

import javafx.application.Application;
import javafx.stage.Modality;
import javafx.stage.Stage;
import javafx.collections.ObservableList;
import vue.FenAffichageSalle;
import vue.FenImpressionBillet;
import vue.FenReservation;
import modele.*;

import java.util.ArrayList;

public class Main extends Application {
    private FenReservation fReservation;
    private FenImpressionBillet fImpressionBillet;
    private FenAffichageSalle fAffichageSalle;

    @Override
    public void start(Stage primaryStage) throws Exception {
/*        ArrayList<Reservation> reservations = createTestData();*/
        
        fReservation = new FenReservation();
        fReservation.initModality(Modality.APPLICATION_MODAL);

        fImpressionBillet = new FenImpressionBillet();
        fImpressionBillet.initModality(Modality.APPLICATION_MODAL);

        fAffichageSalle = new FenAffichageSalle();
        fAffichageSalle.initModality(Modality.APPLICATION_MODAL);

        fReservation.show();
    }

    public static void impressionBillet(ObservableList<Billet> billets) throws Exception {
        FenImpressionBillet fImpressionBillet = new FenImpressionBillet();
        fImpressionBillet.initModality(Modality.APPLICATION_MODAL);
        fImpressionBillet.show();
        fImpressionBillet.getCtrl().setBillets(billets);
    }
/*
    private ArrayList<Reservation> createTestData() {
        Client client = new Client("Doe", "John", "Paris", "1 rue de la paix", "0606060606", "john.doe@gmail.com", "1");
        Representation representation = new Representation("2020-12-25", "20:00", false);
        Reservation reservation = new Reservation("1", "2020-12-25", "2020-12-24", client, representation);
        Fauteuil fauteuil = new Fauteuil("1", "1");
        Billet billet = new Billet("1", client, fauteuil, representation);
        reservation.ajouterBillet(billet);
        ArrayList<Reservation> reservations = new ArrayList<>();
        reservations.add(reservation);
        return reservations;
    }
*/
    public static void main(String[] args) {
        launch(args);
    }
}
