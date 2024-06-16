package vue;

import java.io.IOException;
import javafx.scene.Scene;
import javafx.scene.layout.Pane;
import javafx.stage.Stage;
import javafx.fxml.FXMLLoader;
import modele.*;

public class FenAffichageSalle extends Stage {
    CtrlAffichageSalle ctrl;
    public FenAffichageSalle() throws IOException {
        this.setTitle("Affichage de la salle");
        this.setResizable(false);
        Scene laScene = new Scene(creerSceneGraph());
        this.setScene(laScene);
    }
    
    private Pane creerSceneGraph() throws IOException {
        FXMLLoader loader = new FXMLLoader();
        loader.setLocation(getClass().getResource("/fenetresalle.fxml"));
        Pane root = loader.load();
        ctrl = loader.getController();
        return root;
   }

    public CtrlAffichageSalle getCtrl() {
         return ctrl;
    }

}
    
