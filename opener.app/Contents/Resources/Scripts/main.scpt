FasdUAS 1.101.10   ��   ��    k             i         I     �� 	��
�� .GURLGURLnull��� ��� TEXT 	 o      ���� 0 this_url this_URL��    k     > 
 
     l     ��  ��    K E When the link is clicked in thewebpage, this handler will be passed      �   �   W h e n   t h e   l i n k   i s   c l i c k e d   i n   t h e w e b p a g e ,   t h i s   h a n d l e r   w i l l   b e   p a s s e d        l     ��  ��    5 / the URL that triggered the action, similar to:     �   ^   t h e   U R L   t h a t   t r i g g e r e d   t h e   a c t i o n ,   s i m i l a r   t o :      l     ��  ��    B <> yourURLProtocol://yourBundleIdentifier?key=value&key=value     �   x >   y o u r U R L P r o t o c o l : / / y o u r B u n d l e I d e n t i f i e r ? k e y = v a l u e & k e y = v a l u e      l     ��  ��      >display dialog (this_URL)     �   4 > d i s p l a y   d i a l o g   ( t h i s _ U R L )       l     ��������  ��  ��      ! " ! l     �� # $��   #   EXTRACT ARGUMENTS    $ � % % $   E X T R A C T   A R G U M E N T S "  & ' & r      ( ) ( l    	 *���� * I    	���� +
�� .sysooffslong    ��� null��   + �� , -
�� 
psof , m     . . � / /  ? - �� 0��
�� 
psin 0 o    ���� 0 this_url this_URL��  ��  ��   ) o      ���� 0 x   '  1 2 1 r     3 4 3 n     5 6 5 7    �� 7 8
�� 
ctxt 7 l    9���� 9 [     : ; : o    ���� 0 x   ; m    ���� ��  ��   8 m    ������ 6 o    ���� 0 this_url this_URL 4 l      <���� < o      ���� 0 locparam  ��  ��   2  = > = r    $ ? @ ? I    "�� A���� 0 
decodetext 
decodeText A  B�� B o    ���� 0 locparam  ��  ��   @ o      ���� 0 booklocation   >  C D C l  % %�� E F��   E , &>display dialog (booklocation as text)    F � G G L > d i s p l a y   d i a l o g   ( b o o k l o c a t i o n   a s   t e x t ) D  H�� H O   % > I J I k   ) = K K  L M L r   ) 1 N O N c   ) / P Q P l  ) - R���� R 4   ) -�� S
�� 
psxf S o   + ,���� 0 booklocation  ��  ��   Q m   - .��
�� 
alis O o      ���� 0 book   M  T U T I  2 7������
�� .miscactvnull��� ��� obj ��  ��   U  V�� V I  8 =�� W��
�� .miscmvisnull���     obj  W o   8 9���� 0 book  ��  ��   J m   % & X X�                                                                                  MACS  alis    @  Macintosh HD                   BD ����
Finder.app                                                     ����            ����  
 cu             CoreServices  )/:System:Library:CoreServices:Finder.app/    
 F i n d e r . a p p    M a c i n t o s h   H D  &System/Library/CoreServices/Finder.app  / ��  ��     Y Z Y l     ��������  ��  ��   Z  [ \ [ l     �� ] ^��   ] � �>open location "ebooklib://at.grendel.ebooklib?/Users/thomas/Books/violetshadows/...Fucking%20Tinkers/_..Fucking%20Tinkers-fsv_4051.epub"    ^ � _ _ > o p e n   l o c a t i o n   " e b o o k l i b : / / a t . g r e n d e l . e b o o k l i b ? / U s e r s / t h o m a s / B o o k s / v i o l e t s h a d o w s / . . . F u c k i n g % 2 0 T i n k e r s / _ . . F u c k i n g % 2 0 T i n k e r s - f s v _ 4 0 5 1 . e p u b " \  ` a ` l     ��������  ��  ��   a  b c b i     d e d I      �� f���� 40 decodecharacterhexstring decodeCharacterHexString f  g�� g o      ���� 0 thecharacters theCharacters��  ��   e k     a h h  i j i s      k l k o     ���� 0 thecharacters theCharacters l J       m m  n o n o      ���� 20 theidentifyingcharacter theIdentifyingCharacter o  p q p o      ���� 00 themultipliercharacter theMultiplierCharacter q  r�� r o      ���� .0 theremaindercharacter theRemainderCharacter��   j  s t s r     u v u m     w w � x x  1 2 3 4 5 6 7 8 9 A B C D E F v o      ���� 0 
thehexlist 
theHexList t  y z y Z    8 { |�� } { E   " ~  ~ m      � � � � �  A B C D E F  o     !���� 00 themultipliercharacter theMultiplierCharacter | r   % 0 � � � I  % .���� �
�� .sysooffslong    ��� null��   � �� � �
�� 
psof � o   ' (���� 00 themultipliercharacter theMultiplierCharacter � �� ���
�� 
psin � o   ) *���� 0 
thehexlist 
theHexList��   � o      ���� *0 themultiplieramount theMultiplierAmount��   } r   3 8 � � � c   3 6 � � � o   3 4���� 00 themultipliercharacter theMultiplierCharacter � m   4 5��
�� 
long � o      ���� *0 themultiplieramount theMultiplierAmount z  � � � Z   9 R � ��� � � E  9 < � � � m   9 : � � � � �  A B C D E F � o   : ;���� .0 theremaindercharacter theRemainderCharacter � r   ? J � � � I  ? H���� �
�� .sysooffslong    ��� null��   � �� � �
�� 
psof � o   A B���� .0 theremaindercharacter theRemainderCharacter � �� ���
�� 
psin � o   C D���� 0 
thehexlist 
theHexList��   � o      ���� (0 theremainderamount theRemainderAmount��   � r   M R � � � c   M P � � � o   M N���� .0 theremaindercharacter theRemainderCharacter � m   N O��
�� 
long � o      ���� (0 theremainderamount theRemainderAmount �  � � � r   S Z � � � [   S X � � � l  S V ����� � ]   S V � � � o   S T���� *0 themultiplieramount theMultiplierAmount � m   T U���� ��  ��   � o   V W���� (0 theremainderamount theRemainderAmount � o      ����  0 theasciinumber theASCIINumber �  ��� � L   [ a � � l  [ ` ����� � I  [ `�� ���
�� .sysontocTEXT       shor � o   [ \����  0 theasciinumber theASCIINumber��  ��  ��  ��   c  � � � l     ��������  ��  ��   �  ��� � i     � � � I      �� ����� 0 
decodetext 
decodeText �  ��� � o      ���� 0 thetext theText��  ��   � k     y � �  � � � r      � � � m     ��
�� boovfals � o      ���� 0 flaga flagA �  � � � r     � � � m    ��
�� boovfals � o      ���� 0 flagb flagB �  � � � r     � � � m    	 � � � � �   � o      ���� $0 thetempcharacter theTempCharacter �  � � � r     � � � J    ����   � o      ���� $0 thecharacterlist theCharacterList �  � � � X    t ��� � � k   ! o � �  � � � r   ! & � � � n   ! $ � � � 1   " $��
�� 
pcnt � o   ! "���� *0 thecurrentcharacter theCurrentCharacter � o      ���� *0 thecurrentcharacter theCurrentCharacter �  ��� � Z   ' o � � � � � =  ' * � � � o   ' (���� *0 thecurrentcharacter theCurrentCharacter � m   ( ) � � � � �  % � r   - 0 � � � m   - .��
�� boovtrue � o      �� 0 flaga flagA �  � � � =  3 6 � � � o   3 4�~�~ 0 flaga flagA � m   4 5�}
�} boovtrue �  � � � k   9 D � �  � � � r   9 < � � � o   9 :�|�| *0 thecurrentcharacter theCurrentCharacter � o      �{�{ $0 thetempcharacter theTempCharacter �  � � � r   = @ � � � m   = >�z
�z boovfals � o      �y�y 0 flaga flagA �  ��x � r   A D � � � m   A B�w
�w boovtrue � o      �v�v 0 flagb flagB�x   �  � � � =  G J � � � o   G H�u�u 0 flagb flagB � m   H I�t
�t boovtrue �  ��s � k   M h � �  � � � r   M \ � � � I   M Y�r ��q�r 40 decodecharacterhexstring decodeCharacterHexString �  ��p � c   N U � � � l  N S ��o�n � b   N S � � � b   N Q �  � m   N O �  %  o   O P�m�m $0 thetempcharacter theTempCharacter � o   Q R�l�l *0 thecurrentcharacter theCurrentCharacter�o  �n   � m   S T�k
�k 
TEXT�p  �q   � n        ;   Z [ o   Y Z�j�j $0 thecharacterlist theCharacterList �  r   ] ` m   ] ^		 �

   o      �i�i $0 thetempcharacter theTempCharacter  r   a d m   a b�h
�h boovfals o      �g�g 0 flaga flagA �f r   e h m   e f�e
�e boovfals o      �d�d 0 flagb flagB�f  �s   � r   k o o   k l�c�c *0 thecurrentcharacter theCurrentCharacter n        ;   m n o   l m�b�b $0 thecharacterlist theCharacterList��  �� *0 thecurrentcharacter theCurrentCharacter � o    �a�a 0 thetext theText � �` L   u y c   u x o   u v�_�_ $0 thecharacterlist theCharacterList m   v w�^
�^ 
TEXT�`  ��       �]�]   �\�[�Z
�\ .GURLGURLnull��� ��� TEXT�[ 40 decodecharacterhexstring decodeCharacterHexString�Z 0 
decodetext 
decodeText �Y �X�W�V
�Y .GURLGURLnull��� ��� TEXT�X 0 this_url this_URL�W   �U�T�S�R�Q�U 0 this_url this_URL�T 0 x  �S 0 locparam  �R 0 booklocation  �Q 0 book   �P .�O�N�M�L�K X�J�I�H�G
�P 
psof
�O 
psin�N 
�M .sysooffslong    ��� null
�L 
ctxt�K 0 
decodetext 
decodeText
�J 
psxf
�I 
alis
�H .miscactvnull��� ��� obj 
�G .miscmvisnull���     obj �V ?*���� E�O�[�\[Z�k\Zi2E�O*�k+ E�O� *�/�&E�O*j 
O�j U �F e�E�D !�C�F 40 decodecharacterhexstring decodeCharacterHexString�E �B"�B "  �A�A 0 thecharacters theCharacters�D    �@�?�>�=�<�;�:�9�@ 0 thecharacters theCharacters�? 20 theidentifyingcharacter theIdentifyingCharacter�> 00 themultipliercharacter theMultiplierCharacter�= .0 theremaindercharacter theRemainderCharacter�< 0 
thehexlist 
theHexList�; *0 themultiplieramount theMultiplierAmount�: (0 theremainderamount theRemainderAmount�9  0 theasciinumber theASCIINumber! �8 w ��7�6�5�4�3 ��2�1
�8 
cobj
�7 
psof
�6 
psin�5 
�4 .sysooffslong    ��� null
�3 
long�2 
�1 .sysontocTEXT       shor�C b�E[�k/EQ�Z[�l/EQ�Z[�m/EQ�ZO�E�O� *��� E�Y ��&E�O� *��� E�Y ��&E�O�� �E�O�j 
 �0 ��/�.#$�-�0 0 
decodetext 
decodeText�/ �,%�, %  �+�+ 0 thetext theText�.  # �*�)�(�'�&�%�* 0 thetext theText�) 0 flaga flagA�( 0 flagb flagB�' $0 thetempcharacter theTempCharacter�& $0 thecharacterlist theCharacterList�% *0 thecurrentcharacter theCurrentCharacter$ 
 ��$�#�"�! �� �	
�$ 
kocl
�# 
cobj
�" .corecnte****       ****
�! 
pcnt
�  
TEXT� 40 decodecharacterhexstring decodeCharacterHexString�- zfE�OfE�O�E�OjvE�O b�[��l kh ��,E�O��  eE�Y >�e  �E�OfE�OeE�Y *�e   *�%�%�&k+ �6FO�E�OfE�OfE�Y ��6F[OY��O��& ascr  ��ޭ