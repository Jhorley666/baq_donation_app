import 'package:flutter/material.dart';

class MyDonationsTab extends StatelessWidget {
  const MyDonationsTab({super.key});

  @override
  Widget build(BuildContext context) {
    // Placeholder for chart and donation history
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: 24),
        const Text(
          'My Donations Overview',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        // Placeholder for chart
        Container(
          height: 180,
          color: Colors.orange[50],
          child: const Center(child: Text('Chart goes here')),
        ),
        const SizedBox(height: 24),
        // Placeholder for donation list
        Expanded(
          child: ListView(
            children: const [
              ListTile(
                leading: Icon(Icons.monetization_on),
                title: Text('Donation on 2024-05-01'),
                subtitle: Text('Amount: \$20.00'),
                trailing: Text('1/5'),
              ),
              ListTile(
                leading: Icon(Icons.monetization_on),
                title: Text('Donation on 2024-04-15'),
                subtitle: Text('Amount: \$10.00'),
                trailing: Text('2/5'),
              ),
              // Add more ListTiles as needed
            ],
          ),
        ),
      ],
    );
  }
}