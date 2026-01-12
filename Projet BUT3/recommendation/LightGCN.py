import torch
import torch.nn as nn
import torch.nn.functional as F


# -----------------------------
#   LightGCN Model
# -----------------------------

class LightGCN(nn.Module):
    def __init__(self, num_users, num_items, embedding_dim, adjacency_matrix, n_layers=2):
        super().__init__()

        self.num_users = num_users
        self.num_items = num_items
        self.embedding_dim = embedding_dim
        self.n_layers = n_layers

        # Embeddings for users and items
        self.user_embedding = nn.Embedding(num_users, embedding_dim)
        self.item_embedding = nn.Embedding(num_items, embedding_dim)

        # Xavier initialization
        nn.init.xavier_uniform_(self.user_embedding.weight)
        nn.init.xavier_uniform_(self.item_embedding.weight)

        # Normalized adjacency matrix (torch sparse)
        self.adj = adjacency_matrix

    def propagate(self):
        """
        Propagation LightGCN :
        Chaque couche ajoute la moyenne des voisins dans le graphe bipartite.
        """
        all_embeddings = []
        
        # Embeddings de base
        users = self.user_embedding.weight
        items = self.item_embedding.weight
        ego_embeddings = torch.cat([users, items], dim=0)  # concat user + item

        all_embeddings.append(ego_embeddings)

        for layer in range(self.n_layers):
            # Propagation via produit matrice sparse
            ego_embeddings = torch.sparse.mm(self.adj, ego_embeddings)
            all_embeddings.append(ego_embeddings)

        # Moyenne des embeddings de toutes les couches
        final_embeddings = torch.stack(all_embeddings, dim=1).mean(dim=1)

        # Split user/item
        final_users = final_embeddings[:self.num_users]
        final_items = final_embeddings[self.num_users:]

        return final_users, final_items

    def forward(self, users, items):
        """
        Prédit le score user-item (dot product).
        """
        final_users, final_items = self.propagate()
        user_vecs = final_users[users]
        item_vecs = final_items[items]
        return (user_vecs * item_vecs).sum(dim=1)


# -----------------------------
# Exemple d'utilisation
# -----------------------------

if __name__ == "__main__":

    # Exemple :
    # 3 users, 4 items
    num_users = 3
    num_items = 4
    embedding_dim = 8

    # Edges : utilisateur a écouté un item
    # Format COO : source_nodes, target_nodes
    user_item_edges = torch.tensor([
        [0, 0, 1, 2],   # users : U1 - T1, U1 - T2, U2 - T3, U3 - T4
        [0, 1, 2, 3]    # items indices transformés plus tard
    ])

    # On convertit en indices globaux (users + items)
    rows = torch.cat([user_item_edges[0], user_item_edges[1] + num_users])
    cols = torch.cat([user_item_edges[1] + num_users, user_item_edges[0]])

    # Matrice d'adjacence bipartite symétrique
    values = torch.ones(len(rows))
    adjacency = torch.sparse_coo_tensor(
        torch.stack([rows, cols]),
        values,
        size=(num_users + num_items, num_users + num_items)
    )

    # Normalisation D^-1/2 A D^-1/2
    deg = torch.sparse.sum(adjacency, dim=1).to_dense()
    deg_inv_sqrt = torch.pow(deg, -0.5)
    deg_inv_sqrt[deg_inv_sqrt == float('inf')] = 0

    # Normalisation
    row, col = adjacency._indices()
    vals = adjacency._values() * deg_inv_sqrt[row] * deg_inv_sqrt[col]
    norm_adj = torch.sparse_coo_tensor(torch.stack([row, col]), vals, adjacency.shape)

    # Construction du modèle
    model = LightGCN(num_users, num_items, embedding_dim, norm_adj, n_layers=2)

    # Exemple de prédiction : score pour U1 et T3
    user = torch.tensor([0])  # U1
    item = torch.tensor([2])  # T3

    score = model(user, item)

    print("Score de recommandation U1 → T3 : ", float(score.detach()))

