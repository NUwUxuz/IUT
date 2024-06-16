package vue;

import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;
import modele.Reservation;

import java.io.IOException;
import java.util.ArrayList;

public class FenReservation extends Stage {

    private CtrlReservation ctrl;

    public FenReservation() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/reservation.fxml"));
            Parent root = loader.load();
            ctrl = loader.getController();
            ctrl.setReservations();
            Scene scene = new Scene(root);
            this.setScene(scene);
            this.setTitle("Réservations");
        } catch (IOException e) {
            e.printStackTrace();
            throw new RuntimeException("Failed to load the FXML file.", e);
        }
    }

    public CtrlReservation getCtrl() {
        return ctrl;
    }
}
