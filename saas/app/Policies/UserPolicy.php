<?php

namespace BaoProd\Workforce\Policies;

use BaoProd\Workforce\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Seuls les admins peuvent voir la liste des utilisateurs
        return $user->type === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Un utilisateur peut voir son propre profil ou les admins peuvent voir tous les profils
        return $user->id === $model->id || $user->type === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seuls les admins peuvent créer des utilisateurs
        return $user->type === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Un utilisateur peut modifier son propre profil ou les admins peuvent modifier tous les profils
        return $user->id === $model->id || $user->type === 'admin';
    }

    /**
     * Determine whether the user can change user type.
     */
    public function changeType(User $user, User $model): bool
    {
        // Seuls les admins peuvent changer le type d'utilisateur
        return $user->type === 'admin';
    }

    /**
     * Determine whether the user can change user status.
     */
    public function changeStatus(User $user, User $model): bool
    {
        // Seuls les admins peuvent changer le statut d'un utilisateur
        // Un utilisateur ne peut pas désactiver son propre compte
        return $user->type === 'admin' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Seuls les admins peuvent supprimer des utilisateurs
        // Un admin ne peut pas se supprimer lui-même
        return $user->type === 'admin' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->type === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->type === 'admin' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage tenant settings.
     */
    public function manageTenant(User $user): bool
    {
        return $user->type === 'admin';
    }
}
