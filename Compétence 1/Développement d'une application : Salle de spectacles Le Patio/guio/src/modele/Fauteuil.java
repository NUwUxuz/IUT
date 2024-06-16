package modele;

import javafx.beans.property.SimpleStringProperty;
import javafx.beans.property.StringProperty;

public class Fauteuil {
    private StringProperty rangee;
    private StringProperty numero;

    public Fauteuil(String rangee, String numero) {
        this.rangee = new SimpleStringProperty(rangee);
        this.numero = new SimpleStringProperty(numero);
    }

    public String getRangee() {
        return rangee.get();
    }
    
    public StringProperty rangeeProperty() {
        return rangee;
    }

    public String getNumero() {
        return numero.get();
    }
    
    public StringProperty numeroProperty() {
        return numero;
    }
}