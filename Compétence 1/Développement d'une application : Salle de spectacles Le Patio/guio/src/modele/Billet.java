package modele;

import javafx.beans.property.SimpleStringProperty;
import javafx.beans.property.StringProperty;

public class Billet {
    private StringProperty numero;
    private Client client;
    private Fauteuil fauteuil;
    private Representation representation;

    public Billet(String numero, Client client, Fauteuil fauteuil, Representation representation) {
    	this.numero = new SimpleStringProperty(numero);
        this.client = client;
        this.fauteuil = fauteuil;
        this.representation = representation;
    }

    public String getNumero() {
        return numero.get();
    }
    
    public StringProperty numeroProperty() {
        return numero;
    }

    public Client getClient() {
        return client;
    }

    public Fauteuil getFauteuil() {
        return fauteuil;
    }

    public Representation getRepresentation() {
        return representation;
    }

    @Override
    public String toString() {
        return "Billet{" +
                "numero='" + numero + '\'' +
                ", client=" + client +
                ", fauteuil=" + fauteuil +
                ", representation=" + representation +
                '}';
    }

    public void imprimer() {
        System.out.println("Billet{" +
                "numero='" + numero + '\'' +
                ", client=" + client +
                ", fauteuil=" + fauteuil +
                ", representation=" + representation +
                '}');
    }
}
