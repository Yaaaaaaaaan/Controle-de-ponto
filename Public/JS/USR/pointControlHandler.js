// ========================
// 📁 pointControlHandler.js
// ========================
import { addPointControl, syncPointControlData } from '../indexedDB/Model.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initial sync of point control data
    syncPointControlData().then(() => {
        console.log('Initial point control data sync completed');
    }).catch(error => {
        console.error('Error during initial point control data sync:', error);
    });

    // Set up periodic sync every 5 minutes
    setInterval(() => {
        syncPointControlData().then(() => {
            console.log('Periodic point control data sync completed');
        }).catch(error => {
            console.error('Error during periodic point control data sync:', error);
        });
    }, 5 * 60 * 1000); // 5 minutes in milliseconds

    // Get the form element
    const pointControlForm = document.querySelector('form[name="insertPointControl"]');

    if (pointControlForm) {
        pointControlForm.addEventListener('submit', async function(event) {
            // Don't prevent default - let the form submit normally to the server

            try {
                // Get the user ID from the form
                const userId = document.getElementById('responseIdInput').value;

                if (!userId) {
                    console.error('User ID not found in the form');
                    return;
                }

                // Create point control data object
                const pointControlData = {
                    id: Date.now().toString(), // Generate a unique ID
                    cod: Date.now().toString(), // Use timestamp as code
                    descricao: 'Presença confirmada', // Default description
                    uidUserFK: userId
                };

                // Store in IndexedDB
                await addPointControl(pointControlData);
                console.log('Point control data stored in IndexedDB');

                // Set up a timer to sync with the server after the form submission completes
                setTimeout(async () => {
                    try {
                        await syncPointControlData();
                        console.log('Point control data synchronized with server');
                    } catch (syncError) {
                        console.error('Error synchronizing point control data:', syncError);
                    }
                }, 2000); // Wait 2 seconds to allow server processing

            } catch (error) {
                console.error('Error storing point control data in IndexedDB:', error);
            }
        });
    } else {
        console.warn('Point control form not found');
    }
});
