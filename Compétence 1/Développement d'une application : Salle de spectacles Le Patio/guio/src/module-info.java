/**
 * 
 */
/**
 * 
 */
module guio {
	requires javafx.base;
	requires javafx.controls;
	requires javafx.fxml;
	requires javafx.graphics;
	opens controlleur to javafx.graphics, javafx.fxml;
	opens modele to javafx.graphics, javafx.fxml;
	opens vue to javafx.graphics, javafx.fxml;
}