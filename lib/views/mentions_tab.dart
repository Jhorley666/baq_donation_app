import 'package:flutter/material.dart';

class MentionsTab extends StatelessWidget {
  const MentionsTab({super.key});

  @override
  Widget build(BuildContext context) {
    // Placeholder for mentions, top users, social, notes
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 24),
          const Text(
            'Mentions & Top Donators',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          // Top users
          ListTile(
            leading: CircleAvatar(child: Text('A')),
            title: const Text('Alice'),
            subtitle: const Text('Top Donator'),
          ),
          ListTile(
            leading: CircleAvatar(child: Text('B')),
            title: const Text('Bob'),
            subtitle: const Text('Top Donator'),
          ),
          const Divider(),
          // Social networks embedded posts (placeholder)
          const Text('Social Networks:', style: TextStyle(fontWeight: FontWeight.bold)),
          Container(
            height: 100,
            color: Colors.orange[50],
            child: const Center(child: Text('Embedded social post here')),
          ),
          const SizedBox(height: 24),
          // Notes and suggestions
          const Text('Notes & Suggestions:', style: TextStyle(fontWeight: FontWeight.bold)),
          TextFormField(
            maxLines: 3,
            decoration: const InputDecoration(
              hintText: 'Leave your suggestion...',
              border: OutlineInputBorder(),
            ),
          ),
        ],
      ),
    );
  }
}