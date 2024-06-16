package vue;

import java.io.IOException;
import javafx.scene.Scene;
import javafx.scene.layout.Pane;
import javafx.stage.Stage;
import javafx.fxml.FXMLLoader;
import modele.*;

public class FenImpressionBillet extends Stage {
    CtrlImpressionBillet ctrl;
    public FenImpressionBillet() throws IOException {
		this.setTitle("Impression du billet");
		this.setResizable(false);
		Scene laScene = new Scene(creerSceneGraph());
		this.setScene(laScene);
    }
    
	private Pane creerSceneGraph() throws IOException {
        FXMLLoader loader = new FXMLLoader();
        loader.setLocation(getClass().getResource("/imprimerbillet.fxml"));
        Pane root = loader.load();
        ctrl = loader.getController();
        return root;
   }

    public CtrlImpressionBillet getCtrl() {
         return ctrl;
    }
    
}
